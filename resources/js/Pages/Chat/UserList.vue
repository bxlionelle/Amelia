<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div class="w-1/3 bg-white border-r border-gray-200">
      <div class="p-4 border-b border-gray-200">

        <!-- Back to Home Button -->
        <Link 
          :href="route('home')" 
          class="inline-flex items-center space-x-1 text-blue-600 hover:text-blue-800 font-medium transition"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m4 4v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6" />
          </svg>
          <span>Back to Home</span>
        </Link>

        <h2 class="text-xl font-semibold text-gray-800">Messages</h2>
        <div class="mt-3 relative">
          <input
            type="text"
            placeholder="Search users..."
            class="w-full py-2 pl-10 pr-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            v-model="searchTerm"
          />
          <div class="absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>
      
      <div class="overflow-y-auto h-full pb-20">
        <ul class="divide-y divide-gray-200">
          <li v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
            <Link 
              :href="route('messages.show', user.id)" 
              class="block px-4 py-3"
              :class="{ 'bg-blue-50': user.id === activeUserId }"
            >
              <div class="flex items-center space-x-3">
                <div class="relative">
                  <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center">
                    <span class="text-lg font-semibold text-gray-600">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                  <span v-if="user.is_online" class="absolute bottom-0 right-0 block w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                </div>
                <div class="flex-1">
                  <div class="flex justify-between">
                    <h3 class="text-sm font-medium text-gray-900">
                      {{ user.name }}
                      <span v-if="user.is_admin" class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded-md">
                        Admin
                      </span>
                    </h3>
                    <span class="text-xs text-gray-500">
                      {{ user.last_message_time || '' }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-500 truncate">
                    {{ user.last_message || 'Start a conversation' }}
                  </p>
                </div>
              </div>
            </Link>
          </li>
          <li v-if="filteredUsers.length === 0" class="py-4 text-center text-gray-500">No users found</li>
        </ul>
      </div>
    </div>
    
    <!-- Main chat area -->
    <div v-if="!activeUserId" class="w-2/3 flex flex-col bg-white">
      <div class="flex-1 flex items-center justify-center">
        <div class="text-center">
          <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
          </div>
          <h3 class="text-xl font-medium text-gray-900">Your Messages</h3>
          <p class="mt-2 text-gray-500 max-w-md mx-auto">
            Select a user from the sidebar to start a conversation or continue an existing one.
          </p>
        </div>
      </div>
    </div>
    
    <slot v-else></slot>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// setInterval(() => {
 //   router.reload()
// }, 5000)

const props = defineProps({
  users: {
    type: Array,
    required: true
  },
  activeUserId: {
    type: Number,
    default: null
  }
});

const searchTerm = ref('');

// Filter users based on search term
const filteredUsers = computed(() => {
  return props.users.filter(user => 
    user.name.toLowerCase().includes(searchTerm.value.toLowerCase())
  );
});
</script>