param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$DrushArgs
)

$env:DRUSH_NO_MIN_PHP = "1"
php "$PSScriptRoot\vendor\bin\drush.php" --root="$PSScriptRoot\web" @DrushArgs
