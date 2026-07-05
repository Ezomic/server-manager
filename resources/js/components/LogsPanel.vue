<script setup lang="ts">
import { onUnmounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import logRoutes from '@/routes/servers/logs';

const props = defineProps<{ serverId: number }>();

const path = ref('/var/log/nginx/error.log');
const output = ref('');
const error = ref('');
const loading = ref(false);
const following = ref(false);
let poll: ReturnType<typeof setInterval> | null = null;

const fetchTail = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch(
            `${logRoutes.index(props.serverId).url}?path=${encodeURIComponent(path.value)}&lines=200`,
            { headers: { Accept: 'application/json' } },
        );

        if (!response.ok) {
            const body = await response.json().catch(() => null);

            throw new Error(body?.message || `HTTP ${response.status}`);
        }

        const body = await response.json();

        if (!body.successful) {
            throw new Error(body.output);
        }

        output.value = body.output;
    } catch (e) {
        error.value = `Couldn't read log: ${e instanceof Error ? e.message : e}`;
    } finally {
        loading.value = false;
    }
};

const toggleFollow = () => {
    following.value = !following.value;

    if (following.value) {
        fetchTail();
        poll = setInterval(fetchTail, 3000);
    } else if (poll) {
        clearInterval(poll);
        poll = null;
    }
};

onUnmounted(() => {
    if (poll) {
        clearInterval(poll);
    }
});
</script>

<template>
    <div class="rounded-xl border">
        <div class="flex items-center gap-2 border-b p-4">
            <h2 class="mr-2 font-medium">Logs</h2>
            <Input v-model="path" class="max-w-md font-mono text-xs" />
            <Button
                variant="outline"
                size="sm"
                :disabled="loading"
                @click="fetchTail"
            >
                Tail
            </Button>
            <Button
                :variant="following ? 'default' : 'outline'"
                size="sm"
                @click="toggleFollow"
            >
                {{ following ? 'Stop following' : 'Follow' }}
            </Button>
        </div>

        <p v-if="error" class="border-b p-4 text-sm text-destructive">
            {{ error }}
        </p>

        <pre
            v-if="output"
            class="max-h-96 overflow-y-auto p-4 text-xs whitespace-pre-wrap"
            >{{ output }}</pre>
        <p
            v-else-if="!loading && !error"
            class="p-4 text-sm text-muted-foreground"
        >
            Enter a log path and click tail.
        </p>
    </div>
</template>
