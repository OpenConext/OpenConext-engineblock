#!/usr/bin/env bash

# format one log line
function format_line {
    # determine whether to append continuation character (\c)
    # to log message, do not when we're glueing a split message
    # into one continuous message
    if [[ "$line" =~ (.*)!CHUNKSTART\>(.*) ]]; then
        # display first chunk + line continue
        echo -e "${BASH_REMATCH[1]}${BASH_REMATCH[2]}\c"
    else 
        if [[ "$line" =~ .*!CHUNK\>(.*) ]]; then
            # continue with next chunk
            echo -e "${BASH_REMATCH[1]}\c"
        else
            if [[ "$line" =~ .*!CHUNKEND\>(.*) ]]; then
                # display last chunk + new line
                echo -e "${BASH_REMATCH[1]}"
            else
                # this message is not split, simply display
                echo -e "$line"
            fi
        fi
    fi
}

# bulk format_line
function format_lines {
    for line in "${lines[@]}"; do
        format_line "$line"
    done
}

# Find, format and echo all log lines related to given
# session- or request ID. We need a two-pass search on STDIN
# to make sure we include log items logged before a session
# was started.
function find_related {
    sids=()
    rids=()

    for line in "${lines[@]}"; do
        if [[ "$line" =~ \[([a-zA-Z0-9 ]+)\]\[([a-zA-Z0-9 ]+)\] ]]; then
            SESSION_ID="${BASH_REMATCH[1]}"
            REQUEST_ID="${BASH_REMATCH[2]}"

            if [[ "$SESSION_ID" == "$1" || "$REQUEST_ID" == "$1" ]]; then
                if [[ "$SESSION_ID" != "no session" ]]; then
                    match=$(echo "${sids[@]:0}" | grep -o "$SESSION_ID") 
                    [[ -z $match ]] && sids[${#sids[@]}]="$SESSION_ID"
                fi

                match=$(echo "${rids[@]:0}" | grep -o "$REQUEST_ID")  
                [[ -z $match ]] && rids[${#rids[@]}]="$REQUEST_ID"
            fi
        fi
    done

    for line in "${lines[@]}"; do
        if [[ "$line" =~ \[([a-zA-Z0-9 ]+)\]\[([a-zA-Z0-9 ]+)\] ]]; then
            SESSION_ID="${BASH_REMATCH[1]}"
            REQUEST_ID="${BASH_REMATCH[2]}"

            for id in "${sids[@]}"; do
                if [[ "$id" == "$SESSION_ID" ]]; then             
                    match=$(echo "${rids[@]:0}" | grep -o "$REQUEST_ID")  
                    [[ -z $match ]] && rids[${#rids[@]}]="$REQUEST_ID"
                fi
            done
        fi
    done
}

# format all lines found by find_related
function format_related {
    for line in "${lines[@]}"; do
        if [[ "$line" =~ \[([a-zA-Z0-9 ]+)\]\[([a-zA-Z0-9 ]+)\] ]]; then
            REQUEST_ID="${BASH_REMATCH[2]}"

            for id in "${rids[@]}"; do
                if [[ "$id" == "$REQUEST_ID" ]]; then
                    format_line "$line"
                fi
            done
        fi
    done
}

# display summary of find_related results without
# showing log entries
function summarize_related {
    echo "Found ${#sids[*]} session(s): "
    ct=0
    for id in "${sids[@]}"; do
        for line in "${lines[@]}"; do
            if [[ "$line" =~ \[([a-zA-Z0-9 ]+)\]\[([a-zA-Z0-9 ]+)\] ]]; then
                if [[ "${BASH_REMATCH[1]}" == "$id" ]]; then
                    ct=`expr $ct + 1`
                fi
            fi
        done

        echo "  $id"
    done
    echo "  in $ct log messages"

    echo
    echo "Found ${#rids[*]} request(s): "
    ct=0
    for id in "${rids[@]}"; do
        for line in "${lines[@]}"; do
            if [[ "$line" =~ \[([a-zA-Z0-9 ]+)\]\[([a-zA-Z0-9 ]+)\] ]]; then
                if [[ "${BASH_REMATCH[2]}" == "$id" ]]; then
                    ct=`expr $ct + 1`
                fi
            fi
        done

        echo "  $id"
    done
    echo "  in $ct log messages"
}

# read from stdin
#lines=`cat /dev/stdin`
lines=()
while read -r line; do
  lines[${#lines[@]}]="$line"
done

case $1 in
    --format)
        format_lines lines
        ;;
    --find)
        if [[ -n $2 ]]; then
            find_related $2 lines
            format_related lines
        else
            echo "Usage: $0 --find [session- or request ID]"
        fi
        ;;
    --summarize)
        if [[ -n $2 ]]; then
            find_related $2 lines
            summarize_related lines
        else
            echo "Usage: $0 --summarize [session- or request ID]"
        fi
        ;;
    *)
        echo "Usage: $0 options" 
        echo "         --format         Format all lines from STDIN"
        echo "         --find [id]      Format all lines related to session or request ID"
        echo "         --summarize [id] Display summary of log lines related to [id]"
        ;;
esac
