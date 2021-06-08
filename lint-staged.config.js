module.exports = {
  '**/*.(php|inc)': ['php -l'],
  '**/*.php': ['php vendor/bin/php-cs-fixer fix --dry-run --config .php-cs-fixer.php --allow-risky=yes --stop-on-violation --using-cache=no'],
};
