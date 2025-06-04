#!/bin/bash

PILLAR_WIDTH=3
PILLAR_HEIGHT=2
BOTTOM_LAYER_OF_SNOW_HEIGHT=1

REDUCE_HEIGHT=false
REDUCE_WIDTH=false


draw_tree() {

    local height=$1
    local width=$2

    local top_layer=0
    local bottom_layer=0

    is_parameters_valid "$height" "$width"

    local is_valid=$?

    if [[ $is_valid -ne 0 ]]; then
        echo "Error: Tree cannot be drawn with the specified height and width." >&2
        exit 1
    fi

    if [[ $REDUCE_HEIGHT == "true" ]]; then
        ((height--))
    fi

    if [[ $REDUCE_WIDTH == "true" ]]; then
        ((width--))
    fi

    max_width_of_layers=$((1 + 2 * (top_layer - 1)))

    draw_top_lower_layers "$top_layer" "$lower_layer" "$max_width_of_layers"
    draw_pillar "$max_width_of_layers"
    draw_snow_layer "$max_width_of_layers"
    
}

is_parameters_valid() {

    local height=$1
    local width=$2

    if [[ $height -lt 8 ]]; then
        echo "Error: Height must be at least 8." >&2
        exit 1
    fi

    if [[ $width -lt 7 ]]; then
        echo "Error: Width must be at least 7." >&2
        exit 1
    fi

    local layer1_layer2=$((height - PILLAR_HEIGHT - BOTTOM_LAYER_OF_SNOW_HEIGHT))

    if ((layer1_layer2 % 2 == 0)); then
        REDUCE_HEIGHT="true"
        ((layer1_layer2--))
    fi

    if ((width % 2 == 0)); then
        REDUCE_WIDTH="true"
        ((width--))
    fi

    if [[ $layer1_layer2 -ne $((width - 2)) ]]; then
        return 1
    fi

    top_layer=$((layer1_layer2 / 2 + 1))
    lower_layer=$((layer1_layer2 - top_layer))

    if [[ $top_layer -lt 2 || $lower_layer -lt 2 ]]; then
        return 1
    fi
    
    return 0
}

draw_top_lower_layers() {
    local top_layer_height=$1
    local lower_layer_height=$2
    local max_width_of_layers=$3

    local min_width_for_top_layer=1
    local min_width_for_lower_layer=3
    local step=2
    local empty_space=1
    local last_symbol='*'

    for ((i = 0; i < top_layer_height; i++)); do
        local stars=$((min_width_for_top_layer + i * step))
        local spaces=$(( (max_width_of_layers - stars) / 2 ))
        local symbol
        if ((i % 2 == 0)); then
            symbol="*"
        else
            symbol="#"
        fi

        printf "%*s%*s" $empty_space "" $spaces ""
        printf "%${stars}s\n" | tr " " "$symbol"

        last_symbol=$symbol
    done

    local first_lower_symbol
    if [[ $last_symbol == "*" ]]; then
        first_lower_symbol="#"
    else
        first_lower_symbol="*"
    fi

    for i in $(seq 0 $((lower_layer_height - 1))); do
        local stars=$((min_width_for_lower_layer + i * step))
        local spaces=$(( (max_width_of_layers - stars) / 2 ))
        local symbol
        if ((i % 2 == 0)); then
            symbol=$first_lower_symbol
        else
            if [[ $first_lower_symbol == "*" ]]; then
                symbol="#"
            else
                symbol="*"
            fi
        fi

        printf "%*s%*s" $empty_space "" $spaces ""
        printf "%${stars}s\n" | tr " " "$symbol"
    done
}

draw_pillar() {
    local max_width_of_layers=$1
    local pillar_width=3
    local pillar_height=2
    local pillar_spaces=$(( (max_width_of_layers - pillar_width) / 2 ))
    local empty_space=1

    i=0
    while [ $i -lt $pillar_height ]; do
        printf "%*s" $empty_space ""
        printf "%*s" $pillar_spaces ""
        printf "%${pillar_width}s\n" | tr " " "#"
        ((i++))
    done
}


draw_snow_layer() {
    local max_width_of_layers=$1
    local snow_width=$((max_width_of_layers + 2))
    
    local i=0
    until [ $i -ge $snow_width ]; do
        printf "*"
        ((i++))
    done
    echo
}

draw_tree "$1" "$2"
