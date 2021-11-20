<?php

/*
 * This script creates a .env file from expected variables specified in this file.
 * If we find that an expected environment variable is missing, then we notify you and exit with a -1 exit code.
 * Call it with php create-env-file.php [output filepath]
 */

define('REQUIRED_ENV_VARS', [
    "SITE_NAME",
    "AWS_REGION",
    "AWS_KEY_ID",
    "AWS_KEY_SECRET",
    "COGNITO_HOSTED_UI_URL",
    "COGNITO_CLIENT_ID",
    "COGNITO_CLIENT_SECRET",
]);

define('OPTIONAL_ENV_VARS', []);

define('ALL_VARS', [...REQUIRED_ENV_VARS, ...OPTIONAL_ENV_VARS]);


function main(string $outputFilepath)
{
    $env = shell_exec("env");
    $filteredLines = [];
    $lines = explode(PHP_EOL, $env);

    foreach ($lines as $index => $line)
    {
        $parts = explode("=", $line);

        if (in_array($parts[0], ALL_VARS))
        {
            $filteredLines[$parts[0]] = $line;
        }
    }

    foreach (REQUIRED_ENV_VARS as $expectedVariable)
    {
        $missingKeys = array_diff(REQUIRED_ENV_VARS, array_keys($filteredLines));

        if (count($missingKeys) > 0)
        {
            fwrite(STDERR, "Missing required environment variables: " . PHP_EOL);

            foreach ($missingKeys as $missingKey)
            {
                fwrite(STDERR, " - {$missingKey}" . PHP_EOL);
            }

            fwrite(STDERR, "... out of the follwoing env vars: " . PHP_EOL);
            fwrite(STDERR, "{$env}" . PHP_EOL);

            exit(-1);
        }
    }

    $content = implode(PHP_EOL, $filteredLines);
    file_put_contents($outputFilepath, $content);
}

if (count($argv) < 2)
{
    fwrite(STDERR, "Missing expected output filepath as parameter." . PHP_EOL);
    exit(-1);
}

main($argv[1]);