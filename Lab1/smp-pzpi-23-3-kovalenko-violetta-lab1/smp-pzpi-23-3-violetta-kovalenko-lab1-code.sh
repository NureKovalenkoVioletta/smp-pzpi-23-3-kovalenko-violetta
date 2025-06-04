#!/bin/bash

VERSION="1.0"

quiet_mode=false
group_name=""
input_file=""

E_FILE_NOT_FOUND=1
E_FILE_NOT_READABLE=2
E_GROUP_NOT_FOUND=3
E_PROCESSING_ERROR=4

error_exit() {
    echo "Помилка: $1" >&2
    exit "$2"
}

check_file() {
    if [ ! -f "$1" ]; then
        error_exit "Файл '$1' не знайдено" $E_FILE_NOT_FOUND
    fi
    
    if [ ! -r "$1" ]; then
        error_exit "Файл '$1' не доступний для читання" $E_FILE_NOT_READABLE
    fi
}

show_help() {
    echo "Використання:"
    echo "task2 [--help | --version] | [[-q|--quiet] [академ_група] файл_із_cist.csv]"
    echo "Опції:"
    echo "  --help       Показати це повідомлення допомоги"
    echo "  --version    Показати інформацію про версію"
    echo "  -q, --quiet  Підпригнути вивід у стандартний потік"
    echo "Параметри:"
    echo "  академ_група     Назва академічної групи (шаблон)"
    echo "  файл_із_cist.csv  Експортований CSV файл розкладу"
    exit 0
}

show_version() {
    echo "Версія скрипта: $VERSION"
    exit 0
}

display_message() {
    if [ "$quiet_mode" = false ]; then
        echo "$1"
    fi
}

select_file() {
    files=($(ls -t | grep -E '^TimeTable_.._.._20..\.csv'))
    
    if [ ${#files[@]} -eq 0 ]; then
        error_exit "Файли розкладу, що відповідають шаблону, не знайдені." $E_FILE_NOT_FOUND
    fi
    
    display_message "Доступні файли розкладу:"
    
    files+=('ВИХІД')
    
    select choice in "${files[@]}"; do
        if [ "$choice" = 'ВИХІД' ]; then
            exit 0
        elif [ -n "$choice" ]; then
            input_file="$choice"
            check_file "$input_file"
            break
        else
            display_message "Неправильний вибір. Спробуйте ще раз."
        fi
    done
}

get_groups_from_file() {
    local file="$1"
    iconv -f cp1251 -t UTF-8 "$file" | sed 's/\r/\n/g' | \
    awk 'BEGIN { FPAT="([^,]*|\"[^\"]*\")" } NR>1 {
        if ($1 ~ /ПЗПІ-[0-9]{2}-[0-9]{1,2}([^,0-9]|$)/) {
            match($1, /ПЗПІ-[0-9]{2}-[0-9]{1,2}/, a)
            if (a[0] != "") print a[0]
        }
    }' | sort | uniq
}

select_group() {
    display_message "Витягуємо групи з файлу: $input_file"
    
    display_message "Знайдено ${#groups[@]} груп у файлі"
    
    if [ ${#groups[@]} -eq 1 ]; then
        group_name="${groups[0]}"
        display_message "Знайдена лише одна група: $group_name. Вибір виконано автоматично."
    elif [ ${#groups[@]} -gt 1 ]; then
        display_message "Доступні групи:"
        groups+=('ВИХІД')
        
        select choice in "${groups[@]}"; do
            if [ "$choice" = 'ВИХІД' ]; then
                exit 0
            elif [ -n "$choice" ]; then
                group_name="$choice"
                display_message "Обрана група: $group_name"
                break
            else
                display_message "Неправильний вибір. Спробуйте ще раз."
            fi
        done
    else
        display_message "У вибраному файлі групи не знайдено."
        exit 1
    fi
}

check_group_exists() {
    local group_to_check="$1"
    
    for g in "${groups[@]}"; do
        if [ "$g" = "$group_to_check" ]; then
            return 0  
        fi
    done
    
    display_message "Групу '$group_to_check' не знайдено у файлі."
    if [ ${#groups[@]} -eq 1 ]; then
        display_message "У цьому файлі є лише одна група: ${groups[0]}"
    else
        display_message "Доступні групи у файлі:"
        for g in "${groups[@]}"; do
            display_message "- $g"
        done
    fi
    return 1
}

convert_to_google_calendar() {
    if ! date_from_filename=$(echo "$input_file" | grep -o '[0-9]\{2\}_[0-9]\{2\}_20[0-9]\{2\}'); then
        error_exit "Не вдалося визначити дату з імені файлу" $E_PROCESSING_ERROR
    fi
    
    output_file="Google_TimeTable_${date_from_filename}.csv"
    echo "Subject,Start Date,Start Time,End Date,End Time,Description" > "$output_file" || error_exit "Не вдалося створити вихідний файл" $E_PROCESSING_ERROR
    
    display_message "Обробляємо групу: $group_name"
    
    if ! iconv -f cp1251 -t UTF-8 "$input_file" | sed 's/\r/\n/g' | awk -v group="$group_name" -v single_group="$num_groups" '
    BEGIN {
        FPAT="([^,]*|\"[^\"]*\")"
    }
    
    function clean_quotes(str) {
        gsub(/^"/, "", str)
        gsub(/"$/, "", str)
        gsub(/"/, "", str)
        return str
    }
    
    function convert_date(str) {
        str = clean_quotes(str)
        split(str, d, ".")
        return sprintf("%02d/%02d/%04d", d[2], d[1], d[3])
    }
    
    function convert_time(str) {
        str = clean_quotes(str)
        split(str, t, ":")
        h = t[1] + 0
        m = t[2]
        ampm = (h >= 12) ? "PM" : "AM"
        if (h > 12) h -= 12
        if (h == 0) h = 12
        return sprintf("%02d:%s %s", h, m, ampm)
    }
    
    NR > 1 {
        if (single_group == 1 || $1 ~ group) {
            subject = $1
            sub(/^"[^"]* - /, "", subject)
            sub(/"$/, "", subject)
            subject = clean_quotes(subject)
            
            start_date = convert_date($2)
            end_date = convert_date($4)
            
            start_time = convert_time($3)
            end_time = convert_time($5)
            
            description = clean_quotes($12)
            
            entries[NR] = sprintf("%s|%s|%s|%s|%s|%s|%s",
                subject,
                start_date,
                start_time,
                end_date,
                end_time,
                description,
                $1)
        }
    }
    
    END {
        for (i in entries) print entries[i]
    }
    ' | sort -t'|' -k2,2 -k3,3 | awk -F'|' '
    BEGIN {
        delete lecture_count; delete practice_count; delete lab_count
        prev_subject = ""; prev_date = ""; prev_number = 0
    }
    {
        subject = $1
        start_date = $2
        start_time = $3
        end_date = $4
        end_time = $5
        description = $6
        original_subject = $7
        
        subject_name = subject
        sub(/ .*$/, "", subject_name)
        
        if (!(subject_name in lecture_count)) {
            lecture_count[subject_name] = 0
            practice_count[subject_name] = 0
            lab_count[subject_name] = 0
        }
        
        if (subject ~ /Лк/) {
            lecture_count[subject_name]++
            subject = subject "; №" lecture_count[subject_name]
        } else if (subject ~ /Пз/) {
            practice_count[subject_name]++
            subject = subject "; №" practice_count[subject_name]
        } else if (subject ~ /Лб/) {
            if (subject_name == prev_subject && start_date == prev_date) {
                subject = subject "; №" prev_number
            } else {
                lab_count[subject_name]++
                subject = subject "; №" lab_count[subject_name]
                prev_number = lab_count[subject_name]
            }
            prev_subject = subject_name
            prev_date = start_date
        }
        
        printf "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
            subject,
            start_date,
            start_time,
            end_date,
            end_time,
            description
    }
    ' >> "$output_file"; then
        error_exit "Помилка при обробці файлу" $E_PROCESSING_ERROR
    fi
    
    display_message "Розклад було конвертовано і збережено у файлі $output_file"
}

for arg in "$@"; do
    case "$1" in
        --help)
            show_help
            shift
            ;;
        --version)
            show_version
            shift
            ;;
        -q|--quiet)
            quiet_mode=true
            shift
            ;;
        *)
            if [ -z "$input_file" ] && [ -f "$1" ]; then
                input_file="$1"
            elif [ -z "$group_name" ] && [[ "$1" =~ ^ПЗПІ-[0-9]{2}-[0-9]{1,2}$ ]]; then
                group_name="$1"
            fi
            shift
            ;;
    esac
done

if [ -z "$input_file" ]; then
    select_file
else
    check_file "$input_file"
fi

if ! mapfile -t groups < <(get_groups_from_file "$input_file"); then
    error_exit "Помилка при отриманні списку груп з файлу" $E_PROCESSING_ERROR
fi

num_groups=${#groups[@]}

if [ -n "$group_name" ]; then
    if ! check_group_exists "$group_name"; then
        group_name=""
        select_group
    fi
else
    select_group
fi

display_message "Вибрана група: $group_name"

convert_to_google_calendar
