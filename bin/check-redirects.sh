#!/usr/bin/env bash
# Проверка редиректов: цепочки длиннее одного хопа и финальные ответы не 200.
#
#   bin/check-redirects.sh <базовый URL> [файл со списком]
#
# Файл (по умолчанию bin/urls.txt, иначе bin/urls.example.txt) — по строке на URL:
#   /old-page/                 — проверить, что цепочка ≤ 1 хопа и финал 200;
#   /old-page/  /new-page/     — плюс финальный адрес должен совпасть с ожидаемым.
# Пустые строки и строки с # пропускаются.
#
# Код выхода: 0 — всё в порядке, 1 — есть проблемы, 2 — ошибка запуска.
set -uo pipefail

BASE="${1:-}"
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LIST="${2:-}"

if [ -z "$BASE" ]; then
  echo "Использование: bin/check-redirects.sh <базовый URL> [файл со списком]" >&2
  exit 2
fi
BASE="${BASE%/}"
if [ -z "$LIST" ]; then
  LIST="$DIR/urls.txt"
  [ -f "$LIST" ] || LIST="$DIR/urls.example.txt"
fi
if [ ! -f "$LIST" ]; then
  echo "Нет файла $LIST" >&2
  exit 2
fi

problems=0
checked=0

while read -r path expected _ || [ -n "${path:-}" ]; do
  [ -z "${path:-}" ] && continue
  [[ "$path" == \#* ]] && continue
  checked=$((checked + 1))

  url="$path"
  [[ "$url" == http* ]] || url="$BASE$path"

  out=$(curl -sIL --max-redirs 10 --max-time 20 -o /dev/null -w '%{num_redirects} %{http_code} %{url_effective}' "$url") || {
    echo "ОШИБКА  $path → curl не смог получить ответ"
    problems=$((problems + 1))
    continue
  }
  read -r hops code final <<<"$out"

  issues=()
  [ "$hops" -gt 1 ] && issues+=("цепочка: $hops хопа")
  [ "$code" != "200" ] && issues+=("финал $code")
  if [ -n "${expected:-}" ]; then
    want="$expected"
    [[ "$want" == http* ]] || want="$BASE$expected"
    [ "$final" != "$want" ] && issues+=("ожидался $want")
  fi

  if [ ${#issues[@]} -gt 0 ]; then
    problems=$((problems + 1))
    printf 'ПРОБЛЕМА %s → %s [%s хоп., %s] — %s\n' "$path" "$final" "$hops" "$code" "$(IFS='; '; echo "${issues[*]}")"
  else
    printf 'ok       %s → %s [%s хоп., %s]\n' "$path" "$final" "$hops" "$code"
  fi
done <"$LIST"

echo
echo "Проверено: $checked, с проблемами: $problems"
[ "$problems" -eq 0 ]
