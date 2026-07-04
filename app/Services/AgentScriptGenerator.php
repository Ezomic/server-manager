<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Server;

class AgentScriptGenerator
{
    public function generate(Server $server, string $token): string
    {
        $endpoint = route('agent.metrics.store');

        return <<<BASH
#!/usr/bin/env bash
# ServerManager agent installer — run as root on {$server->name}
set -euo pipefail

cat > /usr/local/bin/servermanager-agent <<'AGENT'
#!/usr/bin/env bash
set -euo pipefail

read -r _ u1 n1 s1 i1 _ < /proc/stat
sleep 1
read -r _ u2 n2 s2 i2 _ < /proc/stat
cpu=\$(awk -v u=\$((u2+n2+s2-u1-n1-s1)) -v t=\$((u2+n2+s2+i2-u1-n1-s1-i1)) 'BEGIN{printf "%.2f", (t>0)?100*u/t:0}')
mem_total=\$(awk '/MemTotal/{printf "%d", \$2/1024}' /proc/meminfo)
mem_avail=\$(awk '/MemAvailable/{printf "%d", \$2/1024}' /proc/meminfo)
mem_used=\$((mem_total - mem_avail))
disk_total=\$(df -m / | awk 'NR==2{print \$2}')
disk_used=\$(df -m / | awk 'NR==2{print \$3}')
load=\$(awk '{print \$1}' /proc/loadavg)

curl -fsS -m 10 -X POST "{$endpoint}" \\
  -H "Authorization: Bearer __TOKEN__" \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d "{\\"cpu_percent\\":\$cpu,\\"memory_used_mb\\":\$mem_used,\\"memory_total_mb\\":\$mem_total,\\"disk_used_mb\\":\$disk_used,\\"disk_total_mb\\":\$disk_total,\\"load_avg\\":\$load}"
AGENT

sed -i "s|__TOKEN__|{$token}|" /usr/local/bin/servermanager-agent
chmod 700 /usr/local/bin/servermanager-agent

cat > /etc/systemd/system/servermanager-agent.service <<'UNIT'
[Unit]
Description=ServerManager metrics agent

[Service]
Type=oneshot
ExecStart=/usr/local/bin/servermanager-agent
UNIT

cat > /etc/systemd/system/servermanager-agent.timer <<'UNIT'
[Unit]
Description=Run ServerManager agent every minute

[Timer]
OnBootSec=60
OnUnitActiveSec=60

[Install]
WantedBy=timers.target
UNIT

systemctl daemon-reload
systemctl enable --now servermanager-agent.timer
echo "ServerManager agent installed."
BASH;
    }
}
