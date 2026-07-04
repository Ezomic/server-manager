<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import sshKeyRoutes from '@/routes/ssh-keys';
import type { SshKey } from '@/types/servers';

defineProps<{ sshKeys: SshKey[] }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'SSH keys', href: sshKeyRoutes.index() }],
    },
});

const form = useForm({
    name: '',
    public_key: '',
    private_key: '',
});

const submit = () => {
    form.post(sshKeyRoutes.store().url, {
        onSuccess: () => form.reset(),
    });
};

const destroy = (key: SshKey) => {
    if (
        confirm(`Delete key ${key.name}? Servers using it will lose their key.`)
    ) {
        router.delete(sshKeyRoutes.destroy(key.id));
    }
};
</script>

<template>
    <Head title="SSH keys" />

    <div class="flex flex-col gap-6 p-4">
        <h1 class="text-xl font-semibold">SSH keys</h1>

        <div
            class="overflow-hidden rounded-xl border"
            v-if="$props.sshKeys.length > 0"
        >
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50 text-left">
                        <th class="p-3 font-medium">Name</th>
                        <th class="p-3 font-medium">Servers</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="key in $props.sshKeys"
                        :key="key.id"
                        class="border-b last:border-0"
                    >
                        <td class="p-3 font-medium">{{ key.name }}</td>
                        <td class="p-3 text-muted-foreground">
                            {{ key.servers_count }}
                        </td>
                        <td class="p-3 text-right">
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="destroy(key)"
                                >Delete</Button
                            >
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <form class="flex max-w-xl flex-col gap-4" @submit.prevent="submit">
            <h2 class="font-medium">Add key</h2>
            <div class="grid gap-2">
                <Label for="key-name">Name</Label>
                <Input id="key-name" v-model="form.name" required />
                <p v-if="form.errors.name" class="text-sm text-destructive">
                    {{ form.errors.name }}
                </p>
            </div>
            <div class="grid gap-2">
                <Label for="public_key">Public key</Label>
                <textarea
                    id="public_key"
                    v-model="form.public_key"
                    class="min-h-20 rounded-md border border-input bg-transparent px-3 py-2 font-mono text-xs"
                    required
                ></textarea>
            </div>
            <div class="grid gap-2">
                <Label for="private_key">Private key</Label>
                <textarea
                    id="private_key"
                    v-model="form.private_key"
                    class="min-h-32 rounded-md border border-input bg-transparent px-3 py-2 font-mono text-xs"
                    required
                ></textarea>
                <p class="text-sm text-muted-foreground">
                    Stored encrypted, never shown again.
                </p>
            </div>
            <div>
                <Button type="submit" :disabled="form.processing"
                    >Add key</Button
                >
            </div>
        </form>
    </div>
</template>
