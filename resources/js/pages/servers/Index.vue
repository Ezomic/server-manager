<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import serverRoutes from '@/routes/servers';
import type { Server } from '@/types/servers';

defineProps<{ servers: Server[] }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Servers', href: serverRoutes.index() }],
    },
});

const statusVariant = (status: Server['status']) =>
    status === 'online'
        ? 'default'
        : status === 'offline'
          ? 'destructive'
          : 'secondary';

const destroy = (server: Server) => {
    if (confirm(`Delete ${server.name}?`)) {
        router.delete(serverRoutes.destroy(server.id));
    }
};
</script>

<template>
    <Head title="Servers" />

    <div class="flex flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">Servers</h1>
            <Button as-child>
                <Link :href="serverRoutes.create()">Add server</Link>
            </Button>
        </div>

        <div
            v-if="$props.servers.length === 0"
            class="rounded-xl border p-12 text-center text-muted-foreground"
        >
            No servers yet. Add your first server to get started.
        </div>

        <div v-else class="overflow-hidden rounded-xl border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50 text-left">
                        <th class="p-3 font-medium">Name</th>
                        <th class="p-3 font-medium">Host</th>
                        <th class="p-3 font-medium">Type</th>
                        <th class="p-3 font-medium">Tags</th>
                        <th class="p-3 font-medium">Status</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="server in $props.servers"
                        :key="server.id"
                        class="border-b last:border-0"
                    >
                        <td class="p-3 font-medium">
                            <Link
                                :href="serverRoutes.show(server.id)"
                                class="hover:underline"
                                >{{ server.name }}</Link
                            >
                        </td>
                        <td class="p-3 text-muted-foreground">
                            {{ server.ssh_user }}@{{ server.hostname }}:{{
                                server.port
                            }}
                        </td>
                        <td class="p-3">{{ server.type }}</td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="tag in server.tags ?? []"
                                    :key="tag"
                                    variant="outline"
                                    >{{ tag }}</Badge
                                >
                            </div>
                        </td>
                        <td class="p-3">
                            <Badge :variant="statusVariant(server.status)">{{
                                server.status
                            }}</Badge>
                        </td>
                        <td class="p-3 text-right">
                            <Button variant="ghost" size="sm" as-child>
                                <Link :href="serverRoutes.edit(server.id)"
                                    >Edit</Link
                                >
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="destroy(server)"
                                >Delete</Button
                            >
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
