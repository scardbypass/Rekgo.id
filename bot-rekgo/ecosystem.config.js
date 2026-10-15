export default {
  apps: [{
    name: 'rekgo-bot',
    script: './index.js',
    cwd: '/rekgo/bot-rekgo',
    node_args: '--env-file=.env',
    exec_mode: 'fork',
    instances: 1,
    autorestart: true,
    watch: false,
    max_memory_restart: '350M',
    min_uptime: '10s',
    max_restarts: 10,
    restart_delay: 10000,
    out_file: '/rekgo/bot-rekgo/bot-out.log',
    error_file: '/rekgo/bot-rekgo/bot-error.log'
  }]
};
