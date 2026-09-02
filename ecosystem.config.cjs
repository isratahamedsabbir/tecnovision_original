module.exports = {
    apps: [
        {
            name: "tecnovision_original-queue",
            script: "artisan",
            interpreter: "php",
            args: "queue:work --tries=3 --max-time=3600 --sleep=3",
            cwd: __dirname,
            exec_mode: "fork",
            instances: 1,
            autorestart: true,
            watch: false,
            max_memory_restart: "300M",
            out_file: "storage/logs/pm2-queue-out.log",
            error_file: "storage/logs/pm2-queue-error.log",
            env: {
                APP_ENV: "production",
            },
        },
    ],
};
