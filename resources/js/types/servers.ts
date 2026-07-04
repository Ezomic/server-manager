export interface SshKeyOption {
    id: number;
    name: string;
}

export interface SshKey extends SshKeyOption {
    public_key: string;
    servers_count: number;
    created_at: string;
}

export interface Metric {
    id: number;
    cpu_percent: number;
    memory_used_mb: number;
    memory_total_mb: number;
    disk_used_mb: number;
    disk_total_mb: number;
    load_avg: number;
    recorded_at: string;
}

export interface Server {
    id: number;
    name: string;
    hostname: string;
    port: number;
    ssh_user: string;
    ssh_key_id: number | null;
    ssh_key?: SshKeyOption | null;
    type: 'web' | 'game';
    provider: string | null;
    tags: string[] | null;
    notes: string | null;
    status: 'unknown' | 'online' | 'offline';
    last_seen_at: string | null;
}
