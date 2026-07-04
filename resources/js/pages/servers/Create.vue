<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ServerForm from '@/components/ServerForm.vue';
import servers from '@/routes/servers';
import type { SshKeyOption } from '@/types/servers';

defineProps<{ sshKeys: SshKeyOption[] }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Servers', href: servers.index() },
            { title: 'Add server', href: servers.create() },
        ],
    },
});

const submit = (form: { post: (url: string) => void }) => {
    form.post(servers.store().url);
};
</script>

<template>
    <Head title="Add server" />

    <div class="flex flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold">Add server</h1>
        <ServerForm
            :ssh-keys="sshKeys"
            submit-label="Add server"
            @submit="submit"
        />
    </div>
</template>
