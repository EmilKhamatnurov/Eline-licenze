#!/usr/bin/env bash
# Подключает стартер к сайту LocalWP (или любому WordPress) симлинками.
#
#   bin/link-local.sh <путь к сайту> [slug темы]
#
# Путь — корень сайта LocalWP (~/Local Sites/mysite), его app/public или wp-content.
# Создаёт:
#   wp-content/mu-plugins/sky-baseline   → <repo>/baseline         (симлинк)
#   wp-content/mu-plugins/sky-loader.php ← <repo>/baseline/sky-loader.php (копия)
#   wp-content/themes/<slug>             → <repo>/theme-starter    (симлинк)
#   wp-content/sky-config.php            → <repo>/project/sky-config.php (симлинк)
#
# Существующие файлы и каталоги (не симлинки) не перезаписываются.
# Windows: запускать из Git Bash с включённым режимом разработчика (нужны права на симлинки).
set -euo pipefail

REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SITE="${1:-}"
SLUG="${2:-$(sed -n 's/^Text Domain: *//p' "$REPO/theme-starter/style.css" | tr -d '[:space:]')}"

if [ -z "$SITE" ]; then
  echo "Использование: bin/link-local.sh <путь к сайту> [slug темы]" >&2
  exit 2
fi

SITE="${SITE%/}"
if [ -d "$SITE/app/public/wp-content" ]; then
  WPC="$SITE/app/public/wp-content"
elif [ -d "$SITE/wp-content" ]; then
  WPC="$SITE/wp-content"
elif [ "$(basename "$SITE")" = "wp-content" ] && [ -d "$SITE" ]; then
  WPC="$SITE"
else
  echo "Не найден wp-content в $SITE" >&2
  exit 1
fi

link() {
  local target="$1" path="$2"
  if [ -L "$path" ]; then
    ln -sfn "$target" "$path"
    echo "обновлён: $path"
  elif [ -e "$path" ]; then
    echo "ПРОПУЩЕН: $path уже существует и это не симлинк — уберите его вручную" >&2
    return 1
  else
    ln -s "$target" "$path"
    echo "создан:   $path → $target"
  fi
}

status=0
mkdir -p "$WPC/mu-plugins" "$WPC/themes"

link "$REPO/baseline" "$WPC/mu-plugins/sky-baseline" || status=1
cp "$REPO/baseline/sky-loader.php" "$WPC/mu-plugins/sky-loader.php"
echo "скопирован: $WPC/mu-plugins/sky-loader.php"

link "$REPO/theme-starter" "$WPC/themes/$SLUG" || status=1

if [ ! -f "$REPO/project/sky-config.php" ]; then
  echo "Нет project/sky-config.php — сначала bin/init.sh или cp project/sky-config.example.php project/sky-config.php" >&2
  status=1
else
  link "$REPO/project/sky-config.php" "$WPC/sky-config.php" || status=1
fi

if [ -e "$WPC/mu-plugins/7sky-loader.php" ] || [ -e "$WPC/mu-plugins/7sky-baseline" ]; then
  echo "ВНИМАНИЕ: в mu-plugins осталась старая версия (7sky-loader.php / 7sky-baseline). Удалите её — две копии не загрузятся вместе." >&2
  status=1
fi

if [ ! -d "$WPC/themes/kadence" ]; then
  echo "Напоминание: установите родительскую тему Kadence (wp theme install kadence)."
fi

exit $status
