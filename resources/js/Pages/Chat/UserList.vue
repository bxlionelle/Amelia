<template>
  <div class="max-w-xl mx-auto mt-10">
    <h2 class="text-lg font-bold mb-4">Select a user to chat with:</h2>
    <ul>
      <li v-for="user in users" :key="user.id" class="mb-2">
        <Link :href="route('messages.show', user.id)" class="text-blue-600 hover:underline">
          {{ user.name }} <span v-if="user.is_admin" class="text-red-500">(Admin)</span>
        </Link>
      </li>
    </ul>
  </div>
</template>
<script setup>
import { Link } from '@inertiajs/vue3'
defineProps({ users: Array })
</script>