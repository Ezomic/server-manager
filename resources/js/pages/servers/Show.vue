<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import MetricSparkline from '@/components/MetricSparkline.vue';
import ServicesPanel from '@/components/ServicesPanel.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import serverRoutes, { agentScript } from '@/routes/servers';
import type { Metric, Server } from '@/types/servers';

const props = defineProps<{
    server: Server;
    latestMetric: Metric | null;
    metrics: Metric[];
    hasAgentToken: boolean;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Servers', href: serverRoutes.index() }],
    },
});

const toGb = (mb: number) => (mb / 1024).toFixed(1);
const percent = (used: number, total: number) =>
    total > 0 ? Math.round((used / total) * 100) : 0;

const cpuSeries = computed(() => props.metrics.map((m) => m.cpu_percent));
const memSeries = computed(() =>
    props.metrics.map((m) => percent(m.memory_used_mb, m.memory_total_mb)),
);
const diskSeries = computed(() =>
    props.metrics.map((m) => percent(m.disk_used_mb, m.disk_total_mb)),
);
</script>

<template>
    <Head :title="server.name" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-semibold">{{ server.name }}</h1>
                <Badge
                    :variant="
                        server.status === 'online'
                            ? 'default'
                            : server.status === 'offline'
                              ? 'destructive'
                              : 'secondary'
                    "
                    >{{ server.status }}</Badge
                >
            </div>
            <Button variant="outline" as-child>
                <Link :href="serverRoutes.edit(server.id)">Edit</Link>
            </Button>
        </div>

        <p class="text-sm text-muted-foreground">
            {{ server.ssh_user }}@{{ server.hostname }}:{{ server.port }}
            <span v-if="server.provider"> · {{ server.provider }}</span>
            <span v-if="server.last_seen_at">
                · last seen {{ new Date(server.last_seen_at).toLocaleString() }}
            </span>
        </p>

        <div
            v-if="!hasAgentToken || !latestMetric"
            class="rounded-xl border p-6"
        >
            <h2 class="mb-1 font-medium">Install the monitoring agent</h2>
            <p class="mb-4 text-sm text-muted-foreground">
                Download the install script and run it as root on this server.
                It reports metrics every minute. Generating a new script revokes
                the previous token.
            </p>
            <form :action="agentScript(server.id).url" method="post">
                <input
                    type="hidden"
                    name="_token"
                    :value="$page.props.csrf_token as string"
                />
                <Button type="submit">Download install script</Button>
            </form>
        </div>

        <div v-if="latestMetric" class="grid gap-4 md:grid-cols-3">
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium text-muted-foreground"
                        >CPU</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold">
                        {{ latestMetric.cpu_percent.toFixed(1) }}%
                    </p>
                    <p class="text-sm text-muted-foreground">
                        load {{ latestMetric.load_avg.toFixed(2) }}
                    </p>
                    <div class="mt-2 text-primary">
                        <MetricSparkline :values="cpuSeries" :max="100" />
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium text-muted-foreground"
                        >Memory</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold">
                        {{
                            percent(
                                latestMetric.memory_used_mb,
                                latestMetric.memory_total_mb,
                            )
                        }}%
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ toGb(latestMetric.memory_used_mb) }} /
                        {{ toGb(latestMetric.memory_total_mb) }} GB
                    </p>
                    <div class="mt-2 text-primary">
                        <MetricSparkline :values="memSeries" :max="100" />
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium text-muted-foreground"
                        >Disk</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold">
                        {{
                            percent(
                                latestMetric.disk_used_mb,
                                latestMetric.disk_total_mb,
                            )
                        }}%
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ toGb(latestMetric.disk_used_mb) }} /
                        {{ toGb(latestMetric.disk_total_mb) }} GB
                    </p>
                    <div class="mt-2 text-primary">
                        <MetricSparkline :values="diskSeries" :max="100" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <ServicesPanel :server-id="server.id" />

        <p v-if="server.notes" class="text-sm whitespace-pre-line">
            {{ server.notes }}
        </p>
    </div>
</template>
