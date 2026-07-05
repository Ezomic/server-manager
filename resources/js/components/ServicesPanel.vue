<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import serviceRoutes from '@/routes/servers/services';

interface ServiceEntry {
    name: string;
    type: 'systemd' | 'docker';
    status: string;
    description: string;
}

const props = defineProps<{ serverId: number }>();

const services = ref<ServiceEntry[] | null>(null);
const loading = ref(false);
const error = ref('');
const busy = ref('');
const filter = ref('');

const csrf = () => usePage().props.csrf_token as string;

const load = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch(serviceRoutes.index(props.serverId).url, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        services.value = (await response.json()).services;
    } catch (e) {
        error.value = `Couldn't load services: ${e instanceof Error ? e.message : e}`;
    } finally {
        loading.value = false;
    }
};

const act = async (service: ServiceEntry, action: string) => {
    busy.value = `${service.type}:${service.name}`;
    error.value = '';

    try {
        const response = await fetch(serviceRoutes.store(props.serverId).url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf(),
            },
            body: JSON.stringify({
                type: service.type,
                name: service.name,
                action,
            }),
        });
        const body = await response.json();

        if (!response.ok || !body.successful) {
            throw new Error(body.output || `HTTP ${response.status}`);
        }

        await load();
    } catch (e) {
        error.value = `${action} ${service.name} failed: ${e instanceof Error ? e.message : e}`;
    } finally {
        busy.value = '';
    }
};

const visible = () =>
    (services.value ?? []).filter((s) =>
        s.name.toLowerCase().includes(filter.value.toLowerCase()),
    );
</script>

<template>
    <div class="rounded-xl border">
        <div class="flex items-center justify-between border-b p-4">
            <h2 class="font-medium">Services</h2>
            <div class="flex items-center gap-2">
                <input
                    v-if="services"
                    v-model="filter"
                    placeholder="Filter…"
                    class="h-8 rounded-md border border-input bg-transparent px-2 text-sm"
                />
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="loading"
                    @click="load"
                >
                    <Spinner v-if="loading" class="size-4" />
                    {{ services ? 'Refresh' : 'Load services' }}
                </Button>
            </div>
        </div>

        <p v-if="error" class="border-b p-4 text-sm text-destructive">
            {{ error }}
        </p>

        <div v-if="services" class="max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <tbody>
                    <tr
                        v-for="service in visible()"
                        :key="`${service.type}:${service.name}`"
                        class="border-b last:border-0"
                    >
                        <td class="p-3 font-mono text-xs">
                            {{ service.name }}
                        </td>
                        <td class="p-3">
                            <Badge variant="outline">{{ service.type }}</Badge>
                        </td>
                        <td class="p-3">
                            <Badge
                                :variant="
                                    ['running', 'active'].includes(
                                        service.status,
                                    )
                                        ? 'default'
                                        : 'secondary'
                                "
                                >{{ service.status }}</Badge
                            >
                        </td>
                        <td
                            class="hidden max-w-64 truncate p-3 text-muted-foreground md:table-cell"
                        >
                            {{ service.description }}
                        </td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <Button
                                v-for="action in ['start', 'stop', 'restart']"
                                :key="action"
                                variant="ghost"
                                size="sm"
                                :disabled="
                                    busy === `${service.type}:${service.name}`
                                "
                                @click="act(service, action)"
                                >{{ action }}</Button
                            >
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p
            v-else-if="!loading && !error"
            class="p-4 text-sm text-muted-foreground"
        >
            Services are fetched over SSH on demand.
        </p>
    </div>
</template>
