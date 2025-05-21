<script setup>
import { Link } from '@inertiajs/vue3'
import { defineProps } from 'vue'

const props = defineProps({
  products: Array
})
</script>

<template>
  <div class="max-w-7xl mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">Admin Store Products</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="product in products" :key="product.id" class="border rounded-lg p-4 shadow">
        <h2 class="text-lg font-semibold mb-2">{{ product.name }}</h2>
        <p class="text-gray-600 mb-2">Uploaded by: {{ product.user.name }}</p>
        <p class="text-gray-800 font-bold">₱{{ product.price }}</p>
        <Link :href="route('products.index')"
              class="text-blue-600 hover:underline text-sm mt-2 block">View all products</Link>
      </div>
    </div>
  </div>
</template>
