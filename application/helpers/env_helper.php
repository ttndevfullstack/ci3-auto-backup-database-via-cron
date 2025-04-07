<?php

function isProduction(): bool
{
    return ENVIRONMENT === 'production';
}

function isStaging(): bool
{
    return ENVIRONMENT === 'staging';
}

function isDevelopment(): bool
{
    return ENVIRONMENT === 'development';
}