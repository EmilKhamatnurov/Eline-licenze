#!/usr/bin/env bash
# Новый проект из стартера.
#
#   bin/init.sh <slug> "<Название темы>" [путь к сайту LocalWP]
#
#   slug     — латиница, цифры, дефис: имя каталога темы и text domain (например, advokat-ufa);
#   название — Theme Name в style.css;
#   путь     — если указан, сразу вызывается bin/link-local.sh.
#
# Что делает:
#   1. project/sky-config.example.php → project/sky-config.php (если ещё нет);
#   2. переименовывает тему: Theme Name, Text Domain и text domain в PHP-файлах темы;
#   3. подключает к сайту симлинками.
set -euo pipefail

REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SLUG="${1:-}"
NAME="${2:-}"
SITE="${3:-}"

if [ -z "$SLUG" ] || [ -z "$NAME" ]; then
  echo "Использование: bin/init.sh <slug> \"<Название темы>\" [путь к сайту]" >&2
  exit 2
fi
if ! [[ "$SLUG" =~ ^[a-z0-9]+(-[a-z0-9]+)*$ ]]; then
  echo "slug: только строчная латиница, цифры и дефис — «${SLUG}» не подходит" >&2
  exit 2
fi

THEME="$REPO/theme-starter"
OLD_SLUG="$(sed -n 's/^Text Domain: *//p' "$THEME/style.css" | tr -d '[:space:]')"

# 1. Конфиг проекта
if [ -f "$REPO/project/sky-config.php" ]; then
  echo "project/sky-config.php уже есть — не трогаю"
else
  cp "$REPO/project/sky-config.example.php" "$REPO/project/sky-config.php"
  echo "создан project/sky-config.php — заполните organization, city, schema, analytics"
fi

# 2. Тема. perl -pi одинаково работает на macOS и Linux, в отличие от sed -i.
export SKY_NAME="$NAME" SKY_SLUG="$SLUG" SKY_OLD="$OLD_SLUG"
perl -pi -e 's/^Theme Name:.*$/Theme Name:   $ENV{SKY_NAME}/; s/^Text Domain:.*$/Text Domain:  $ENV{SKY_SLUG}/' "$THEME/style.css"
if [ "$OLD_SLUG" != "$SLUG" ]; then
  find "$THEME" -name '*.php' -print0 | xargs -0 perl -pi -e "s/(['\"])\Q\$ENV{SKY_OLD}\E\1/\${1}\$ENV{SKY_SLUG}\${1}/g"
fi
echo "тема: «${NAME}», text domain $SLUG"

# 3. Подключение к сайту
if [ -n "$SITE" ]; then
  "$REPO/bin/link-local.sh" "$SITE" "$SLUG"
else
  echo "Подключить к сайту: bin/link-local.sh <путь к сайту> $SLUG"
fi

cat <<EOF

Дальше:
  - активируйте Kadence и тему $SLUG, плагин Meta Box (или ACF);
  - Настройки → Постоянные ссылки → «Название записи»;
  - заполните project/sky-config.php; секреты — в wp-config.php (SKY_TG_TOKEN, SKY_TG_CHAT_ID);
  - чек-лист запуска — в README.md.
EOF
