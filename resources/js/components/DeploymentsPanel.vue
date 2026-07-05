<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import deploymentScriptRoutes from '@/routes/servers/deployment-scripts';
import deploymentRoutes from '@/routes/servers/deployments';
import type { Deployment, DeploymentScript } from '@/types/deployments';

const props = defineProps<{
    serverId: number;
    scripts: DeploymentScript[];
}>();

const deployments = ref<Deployment[]>([]);
const running = ref<number | null>(null);
const error = ref('');
const newName = ref('');
const newScript = ref('');
const showForm = ref(false);
let poll: ReturnType<typeof setInterval> | null = null;

const csrf = () => usePage().props.csrf_token as string;

const jsonHeaders = () => ({
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-CSRF-TOKEN': csrf(),
});

const loadHistory = async () => {
    const response = await fetch(deploymentRoutes.index(props.serverId).url, {
        headers: { Accept: 'application/json' },
    });

    if (response.ok) {
        deployments.value = (await response.json()).deployments;
    }
};

const startPolling = () => {
    if (poll) {
        return;
    }

    poll = setInterval(loadHistory, 2000);
};

onUnmounted(() => {
    if (poll) {
        clearInterval(poll);
    }
});

const run = async (script: DeploymentScript) => {
    error.value = '';
    running.value = script.id;

    try {
        const response = await fetch(
            deploymentRoutes.store([props.serverId, script.id]).url,
            { method: 'POST', headers: jsonHeaders() },
        );

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        await loadHistory();
        startPolling();
    } catch (e) {
        error.value = `Couldn't start deployment: ${e instanceof Error ? e.message : e}`;
    } finally {
        running.value = null;
    }
};

const addScript = async () => {
    error.value = '';

    try {
        const response = await fetch(
            deploymentScriptRoutes.store(props.serverId).url,
            {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({
                    name: newName.value,
                    script: newScript.value,
                }),
            },
        );

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        newName.value = '';
        newScript.value = '';
        showForm.value = false;
        window.location.reload();
    } catch (e) {
        error.value = `Couldn't save script: ${e instanceof Error ? e.message : e}`;
    }
};

const statusVariant = (status: Deployment['status']) =>
    status === 'succeeded'
        ? 'default'
        : status === 'failed'
          ? 'destructive'
          : 'secondary';

loadHistory().then(() => {
    if (deployments.value.some((d) => d.status === 'running')) {
        startPolling();
    }
});
</script>

<template>
    <div class="rounded-xl border">
        <div class="flex items-center justify-between border-b p-4">
            <h2 class="font-medium">Deployments</h2>
            <Button variant="outline" size="sm" @click="showForm = !showForm">
                {{ showForm ? 'Cancel' : 'New script' }}
            </Button>
        </div>

        <p v-if="error" class="border-b p-4 text-sm text-destructive">
            {{ error }}
        </p>

        <form
            v-if="showForm"
            class="flex flex-col gap-3 border-b p-4"
            @submit.prevent="addScript"
        >
            <Input v-model="newName" placeholder="Script name" required />
            <textarea
                v-model="newScript"
                class="min-h-24 rounded-md border border-input bg-transparent px-3 py-2 font-mono text-xs"
                placeholder="cd /home/forge/app && git pull && php artisan migrate --force"
                required
            ></textarea>
            <div>
                <Button type="submit" size="sm">Save script</Button>
            </div>
        </form>

        <div
            v-if="scripts.length === 0"
            class="p-4 text-sm text-muted-foreground"
        >
            No deployment scripts yet.
        </div>
        <div v-else class="flex flex-wrap gap-2 border-b p-4">
            <Button
                v-for="script in scripts"
                :key="script.id"
                size="sm"
                :disabled="running === script.id"
                @click="run(script)"
            >
                Run {{ script.name }}
            </Button>
        </div>

        <div v-if="deployments.length > 0" class="max-h-96 overflow-y-auto">
            <div
                v-for="deployment in deployments"
                :key="deployment.id"
                class="border-b p-4 last:border-0"
            >
                <div class="mb-2 flex items-center gap-2 text-sm">
                    <Badge :variant="statusVariant(deployment.status)">{{
                        deployment.status
                    }}</Badge>
                    <span class="font-medium">{{
                        deployment.deployment_script?.name
                    }}</span>
                    <span class="text-muted-foreground"
                        >by {{ deployment.user?.name }} ·
                        {{
                            new Date(deployment.started_at).toLocaleString()
                        }}</span
                    >
                </div>
                <pre
                    v-if="deployment.output"
                    class="max-h-48 overflow-y-auto rounded-md bg-muted p-3 text-xs whitespace-pre-wrap"
                    >{{ deployment.output }}</pre>
            </div>
        </div>
    </div>
</template>
