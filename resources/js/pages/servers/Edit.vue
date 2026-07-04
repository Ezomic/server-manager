<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ServerForm from '@/components/ServerForm.vue';
import servers from '@/routes/servers';
import type { Server, SshKeyOption } from '@/types/servers';

const props = defineProps<{ server: Server; sshKeys: SshKeyOption[] }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Servers', href: servers.index() }],
    },
});

const submit = (form: { put: (url: string) => void }) => {
    form.put(servers.update(props.server.id).url);
};
</script>

<template>
    <Head :title="`Edit ${server.name}`" />

    <div class="flex flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold">Edit {{ server.name }}</h1>
        <ServerForm
            :server="server"
            :ssh-keys="sshKeys"
            submit-label="Save changes"
            @submit="submit"
        />
    </div>
</template>
