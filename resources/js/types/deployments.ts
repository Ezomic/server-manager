export interface DeploymentScript {
    id: number;
    name: string;
    script: string;
}

export interface Deployment {
    id: number;
    status: 'running' | 'succeeded' | 'failed';
    output: string | null;
    started_at: string;
    finished_at: string | null;
    deployment_script?: { id: number; name: string };
    user?: { id: number; name: string };
}
