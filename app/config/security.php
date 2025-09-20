<?php
// config/security.php
return [
    'IpSecurity' => [
        'suspiciousPatterns' => [
            // Path Traversal & LFI Attempts
            '/\.\.[\/\\\\]/',
            '/%2e%2e[\/\\\\]/',
            '/%252e%252e\//',
            '/%c0%ae%c0%ae\//',

            // System Files
            '/\/etc\/passwd/',
            '/\/etc\/shadow/',
            '/boot\.ini/',
            '/\.env$/',
            '/\.git\//',
            '/\.htaccess$/',
            '/web\.config$/',
            '/composer\.json$/',

            // Shell Detection
            '/shell\.php$/',
            '/cmd\.php$/',
            '/eval\s*\(/',
            '/system\s*\(/',
            '/exec\s*\(/',
            '/shell_exec\s*\(/',

            // SQL Injection patterns
            '/\bunion\s+select\b/i',
            '/\bselect\s+.*?\s+from\s+/i',
            '/\binsert\s+into\s+/i',
            '/\bupdate\s+.*?\s+set\s+/i',
            '/\bdelete\s+from\s+/i',
            '/\bdrop\s+table\s+/i',
            '/--\s*$/',
            '/\/\*.*?\*\//',
            '/\bwhere\s+.*?\s*=\s*.*?\s+or\s+/i',
            '/\bexec\s*\(\s*[\'\"].*?[\'\"\)]/i',

            // File Extensions
            '/\.(php[3-7]?|phtml|phps)(\?|$)/i',
            '/\.(asp|aspx|jsp|jspx)(\?|$)/i',
            '/\.(exe|dll|cgi|pl)(\?|$)/i',

            // XSS patterns
            '/<script[^>]*>/i',
            '/<iframe[^>]*>/i',
            '/javascript\s*:/i',
            '/on(error|load|click|mouse\w+)\s*=/i',

            // Common Probes paths
            '/phpinfo\.php(\?|$)/i',
            '/\/wp-(admin|content|includes)\//i',
            '/\/(phpmyadmin|pma|mysql|mysqladmin)\//i',
            '/\/administrator\//i',

            // Sensitive Paths
            '/\/proc\/self\//i',
            '/\/proc\/\d+\//i',
            '/\/tmp\/[^\/]*\.(php|pl|py|rb|sh)(\?|$)/i',
            '/\/var\/tmp\/[^\/]*\.(php|pl|py|rb|sh)(\?|$)/i',

            // Command Injection
            '/[;|&]\s*(wget|curl|bash|sh|nc|netcat|perl|python|php|ruby)\b/i',
            '/\b(wget|curl|bash|sh|nc|netcat)\s+[;|&]/i',
            '/`[^`]+`/',
            '/\$\([^)]+\)/',
            '/\$\{[^}]+\}/',

            // Protocol handlers
            '/^(php|file|data|gopher|expect|phar):\/\//i',
            '/(href|src|action)\s*=\s*[\'"]\s*(php|file|data|gopher|expect|phar):/i',

            // Backup files
            '/\.(bak|old|backup|sql|dump)(\?|$)/i',
            '/\~$/i',
            '/\.swp$/i',

            // Dangerous archive combinations
            '/\.(php|phtml|php[3-7]|asp|aspx|jsp|pl|py|rb|sh|exe)\.(zip|rar|7z|tar|gz)(\?|$)/i',
            '/\.(zip|rar|7z|tar\.gz)\.(php|asp|jsp|sh|exe)(\?|$)/i',
            '/\bshell\.(zip|rar|7z|tar\.gz)(\?|$)/i',
            '/\/(uploads?|temp|tmp|cache)\/.*\.(zip|rar|7z|tar\.gz)(\?|$)/i',

            // Arbitrary File Upload Attempts
            '/\.(phar)(\?|$)/i',
            '/\.(bat|cmd|ps1|vbs|wsf)(\?|$)/i',
            '/\b(upload|remote|shell|backdoor|c99|r57|b374k)\.(php|jsp|asp|pl)/i',

            // LFI/RFI Variants
            '/[?&](file|document|root|path)\s*=\s*(\/|\.\.\/|https?:)/i',
            '/\b(include|require)(_once)?\s*\(\s*[\'"](https?:|\.\.\/|\/)/i',
            '/\bfile_get_contents\s*\(\s*[\'"](https?:|php:|file:|data:)/i',
            '/\/proc\/self\/environ/i',
            '/\/var\/log\/(apache|nginx|httpd)\//i',

            // SQL Injection
            '/\bOR\s+1\s*=\s*1\b/i',
            '/\bAND\s+1\s*=\s*0\b/i',
            '/\bCAST\s*\([^)]+\s+AS\s+/i',
            '/\bCONCAT\s*\([^)]+\)/i',
            '/\bINFORMATION_SCHEMA\./i',
            '/\bSLEEP\s*\(\s*\d+\s*\)/i',
            '/\bBENCHMARK\s*\(\s*\d+\s*,/i',
            '/\bEXTRACTVALUE\s*\(/i',
            '/\bUPDATEXML\s*\(/i',

            // Command Injection
            '/(\||&&)\s*(whoami|id|uname|hostname|pwd|ls)\b/i',
            '/[;&]\s*(cat|less|more|tail|head)\s+\/etc\//i',
            '/\b(chmod|chown)\s+[0-7]{3,4}\s+/i',
            '/\brm\s+-rf\s+/i',
            '/mkfifo\s+/i',
            '/nc\s+-e\s+/i',

            // XSS
            '/%3C(script|iframe|object|embed|img)%3E/i',
            '/<img[^>]+on\w+\s*=/i',
            '/<(body|svg|math)[^>]+on\w+\s*=/i',
            '/style\s*=\s*["\'].*?(expression|javascript|vbscript)/i',
            '/\b(alert|confirm|prompt)\s*\(/i',
            '/document\.(cookie|location|write)/i',
            '/window\.(location|open)/i',
            '/String\.fromCharCode\s*\(/i',
            '/\beval\s*\(/i',
            '/\batob\s*\(/i',

            // SSRF / Port Scanning
            '/(https?|ftp):\/\/(localhost|127\.0\.0\.1|0\.0\.0\.0)/i',
            '/(https?|ftp):\/\/192\.168\.\d{1,3}\.\d{1,3}/i',
            '/(https?|ftp):\/\/10\.\d{1,3}\.\d{1,3}\.\d{1,3}/i',
            '/(https?|ftp):\/\/172\.(1[6-9]|2[0-9]|3[0-1])\.\d{1,3}\.\d{1,3}/i',
            '/:(22|23|25|110|143|445|3389|8080|8443)\b/',
            '/\bgopher:\/\//i',
            '/\bdict:\/\//i',
            '/\bsftp:\/\//i',
            '/\bldap:\/\//i',

            // Specific Probes / Known Vulnerabilities
            '/\/cgi-bin\/(php|test-cgi|printenv)/i',
            '/(test|debug|trace|old)\.php(\?|$)/i',
            '/phpinfo\s*\(\s*\)/i',
            '/\/\.env\.(bak|old|txt|backup)$/i',
            '/\/config\/(database|config)\.yml$/i',
            '/\/(db|database)\.(sql|dump|backup)$/i',
            '/\/\.(svn|git|hg|bzr)\//i',
            '/~(root|admin|administrator)\//',
            '/\/(joomla|wordpress|drupal|magento)\/administrator\//i',
            '/\/cpanel\/$/i',
            '/\/\.aws\/credentials$/i',
            '/\/\.ssh\/id_rsa$/i',
        ],
    ],
];