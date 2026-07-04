<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { Server, SshKeyOption } from '@/types/servers';

const props = defineProps<{
    server?: Server;
    sshKeys: SshKeyOption[];
    submitLabel: string;
}>();

const emit = defineEmits<{
    submit: [form: ReturnType<typeof useForm>];
}>();

const form = useForm({
    name: props.server?.name ?? '',
    hostname: props.server?.hostname ?? '',
    port: props.server?.port ?? 22,
    ssh_user: props.server?.ssh_user ?? 'root',
    ssh_key_id: props.server?.ssh_key_id ?? null,
    type: props.server?.type ?? 'web',
    provider: props.server?.provider ?? '',
    tags: (props.server?.tags ?? []).join(', '),
    notes: props.server?.notes ?? '',
});

const submit = () => {
    emit(
        'submit',
        form.transform((data) => ({
            ...data,
            tags: data.tags
                .split(',')
                .map((tag: string) => tag.trim())
                .filter(Boolean),
        })),
    );
};
</script>

<template>
    <form class="flex max-w-xl flex-col gap-4" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" required />
            <p v-if="form.errors.name" class="text-sm text-destructive">
                {{ form.errors.name }}
            </p>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2 grid gap-2">
                <Label for="hostname">Hostname / IP</Label>
                <Input id="hostname" v-model="form.hostname" required />
                <p v-if="form.errors.hostname" class="text-sm text-destructive">
                    {{ form.errors.hostname }}
                </p>
            </div>
            <div class="grid gap-2">
                <Label for="port">Port</Label>
                <Input
                    id="port"
                    v-model.number="form.port"
                    type="number"
                    min="1"
                    max="65535"
                    required
                />
                <p v-if="form.errors.port" class="text-sm text-destructive">
                    {{ form.errors.port }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="grid gap-2">
                <Label for="ssh_user">SSH user</Label>
                <Input id="ssh_user" v-model="form.ssh_user" required />
            </div>
            <div class="grid gap-2">
                <Label>SSH key</Label>
                <Select v-model="form.ssh_key_id">
                    <SelectTrigger>
                        <SelectValue placeholder="No key selected" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="key in sshKeys"
                            :key="key.id"
                            :value="key.id"
                        >
                            {{ key.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="grid gap-2">
                <Label>Type</Label>
                <Select v-model="form.type">
                    <SelectTrigger>
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="web">Web</SelectItem>
                        <SelectItem value="game">Game</SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div class="grid gap-2">
                <Label for="provider">Provider</Label>
                <Input
                    id="provider"
                    v-model="form.provider"
                    placeholder="Hetzner, DigitalOcean…"
                />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="tags">Tags</Label>
            <Input
                id="tags"
                v-model="form.tags"
                placeholder="production, eu-west"
            />
            <p class="text-sm text-muted-foreground">Comma-separated</p>
        </div>

        <div class="grid gap-2">
            <Label for="notes">Notes</Label>
            <textarea
                id="notes"
                v-model="form.notes"
                class="min-h-24 rounded-md border border-input bg-transparent px-3 py-2 text-sm"
            ></textarea>
        </div>

        <div>
            <Button type="submit" :disabled="form.processing">{{
                submitLabel
            }}</Button>
        </div>
    </form>
</template>
