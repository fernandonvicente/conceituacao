#!/usr/bin/env sh
set -e

# garante .env e chave da aplicação
[ -f .env ] || cp .env.example .env

# sincroniza variáveis do Docker Compose no .env
# (evita o Laravel ler DB_HOST=127.0.0.1 do .env.example)
php -r '
$keys = [
    "DB_CONNECTION", "DB_HOST", "DB_PORT", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD",
    "RABBITMQ_HOST", "RABBITMQ_PORT", "RABBITMQ_USER", "RABBITMQ_PASSWORD", "RABBITMQ_VHOST", "RABBITMQ_QUEUE",
];
$content = file_get_contents(".env");
foreach ($keys as $key) {
    $value = getenv($key);
    if ($value === false) {
        continue;
    }
    $line = $key."=".$value;
    if (preg_match("/^{$key}=.*/m", $content)) {
        $content = preg_replace("/^{$key}=.*/m", $line, $content);
    } else {
        $content .= PHP_EOL.$line.PHP_EOL;
    }
}
file_put_contents(".env", $content);
'

php artisan key:generate --force

# aguarda o MySQL aceitar conexões
echo "Aguardando o MySQL em ${DB_HOST}:${DB_PORT}..."
until php -r "exit(@fsockopen(getenv('DB_HOST'), (int) getenv('DB_PORT')) ? 0 : 1);"; do
    sleep 2
done

php artisan config:clear
php artisan migrate --force
php artisan db:seed --force

php artisan serve --host=0.0.0.0 --port=8000
