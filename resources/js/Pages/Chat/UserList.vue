<script setup>
import { Link, router } from '@inertiajs/vue3';
import { defineProps, ref, computed } from 'vue';

// setInterval(() => {
 //   router.reload()
// }, 5000)

const logout = () => {
    router.post(route('logout'))
}

const props = defineProps({
  users: {
    type: Array,
    required: true
  },
  activeUserId: {
    type: Number,
    default: null
  },
  products: Array,
  auth: Object,
  cart: Object,
  canLogin: Boolean,
  canRegister: Boolean
});

// Function to become admin (for demo/testing)
function becomeAdmin() {
    router.post('/become-admin');
}

// Group products by admin/creator
const productsByAdmin = computed(() => {
  if (!props.products || props.products.length === 0) return []
  
  const grouped = {}
  
  props.products.forEach(product => {
    const adminName = product.creator?.name || product.created_by_user?.name || 'Unknown Admin'
    const adminId = product.created_by || product.creator?.id || 'unknown'
    
    if (!grouped[adminId]) {
      grouped[adminId] = {
        adminName: adminName,
        products: [],
        totalValue: 0
      }
    }
    
    grouped[adminId].products.push(product)
    grouped[adminId].totalValue += parseFloat(product.price) || 0
  })
  
  // Convert to array and sort by total value (highest first)
 // return Object.values(grouped).sort((a, b) => b.totalValue - a.totalValue)
})

// Format currency
const formatPrice = (price) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(price)
}

// Get primary product image
const getProductImage = (product) => {
  if (product.product_images && product.product_images.length > 0) {
    return `/${product.product_images[0].image}`
  }
  return '/images/no-image.png' // fallback image
}

// Get stock status
const getStockStatus = (product) => {
  if (product.quantity > 0) {
    return {
      status: 'In Stock',
      class: 'bg-green-100 text-green-800',
      quantity: product.quantity
    }
  }
  return {
    status: 'Out of Stock',
    class: 'bg-red-100 text-red-800',
    quantity: 0
  }
}

// Truncate description
const truncateDescription = (description, length = 100) => {
  if (!description) return 'No description available'
  return description.length > length 
    ? description.substring(0, length) + '...' 
    : description
}

// Check if route exists (helper function)
const routeExists = (routeName) => {
  try {
    route(routeName, 1) // Test with dummy parameter
    return true
  } catch (error) {
    return false
  }
}

const searchTerm = ref('');

// Filter users based on search term
const filteredUsers = computed(() => {
  return props.users.filter(user => 
    user.name.toLowerCase().includes(searchTerm.value.toLowerCase())
  );
});
</script>

<template>
  <!-- Navigation Header -->
  <nav class="bg-white border-gray-200 dark:bg-gray-900">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
      <Link :href="route('home')" class="flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
        </svg>
        <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">Amelia</span>
      </Link>
      
      <div v-if="canLogin" class="flex items-center md:order-2">
        <div class="mr-4">
          <Link :href="route('cart.view')"
              class="relative inline-flex items-center p-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-6 h-6 ">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            <span class="sr-only">cart</span>
            <div class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-white rounded-full -top-2 -right-2 dark:border-gray-900">
                {{ cart?.data?.count || 0 }}
            </div>
          </Link>
        </div>
        
        <button v-if="auth?.user" type="button"
            class="flex mr-3 text-sm bg-gray-800 rounded-full md:mr-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
            id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown"
            data-dropdown-placement="bottom">
            <span class="sr-only">Open user menu</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                class="w-8 h-8 rounded-full bg-white" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </button>
        
        <div v-else>
            <Link :href="route('login')" type="button"
                class="text-white bg-gradient-to-br from-purple-600 to-blue-500 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center mr-2 mb-2">
            Login</Link>
            <Link :href="route('register')" v-if="canRegister" type="button"
                class="text-white bg-gradient-to-r from-cyan-500 to-blue-500 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-cyan-300 dark:focus:ring-cyan-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center mr-2 mb-2">
            Register</Link>
        </div>

        <!-- Dropdown menu -->
        <div v-if="auth?.user"
            class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow dark:bg-gray-700 dark:divide-gray-600"
            id="user-dropdown">
            <div class="px-4 py-3">
                <span class="block text-sm text-gray-900 dark:text-white">{{ auth.user.name }}</span>
                <span class="block text-sm  text-gray-500 truncate dark:text-gray-400">{{ auth.user.email }}</span>
            </div>
            <ul class="py-2" aria-labelledby="user-menu-button">
                <li>
                    <Link :href="route('messages.list')" class="block py-2 px-4 text-sm hover:bg-gray-100">
                      Private Messages
                    </Link>
                </li>
                <li>
                    <Link :href="route('dashboard')"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                    Dashboard</Link>
                </li>
                <li v-if="auth.user.is_admin == 1">
                    <Link :href="route('admin.dashboard')"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                    Admin Dashboard</Link>
                </li>
                <li v-else>
                    <div class="px-4 py-2 text-sm text-yellow-700 bg-yellow-100 rounded dark:bg-yellow-900 dark:text-yellow-200 mb-2">
                        <strong>Warning:</strong> Only admin users can access the admin dashboard.
                    </div>
                    <form @submit.prevent="becomeAdmin">
                        <button type="submit"
                            class="block w-full px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                            Become an Admin
                        </button>
                    </form>
                </li>
                <li>
                    <button 
                        @click="logout"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white"
                    >
                        Logout
                    </button>
                </li>
            </ul>
        </div>
        
        <button data-collapse-toggle="navbar-user" type="button"
            class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
            aria-controls="navbar-user" aria-expanded="false">
            <span class="sr-only">Open main menu</span>
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 17 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M1 1h15M1 7h15M1 13h15" />
            </svg>
        </button>
      </div>
      
      <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-user">
        <ul class="flex flex-col font-medium p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:flex-row md:space-x-8 md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
            <li>
                <Link :href="route('home')"
                    class="block py-2 pl-3 pr-4 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent 
                        md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 
                        dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">
                    Home
                </Link>
            </li>
            <li>
              <Link
                :href="auth?.user && auth.user.is_admin == 1 ? route('admin.products.index') : route('stores.view')"
                :class="[
                  'block py-2 pl-3 pr-4 font-medium rounded hover:bg-gray-100 md:hover:bg-transparent md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent',
                  route().current('admin.products.index') || route().current('stores.view') ? 'text-blue-700' : 'text-gray-700'
                ]"
              >
                Stores
              </Link>

            </li>
            <li>
            <Link :href="route('products.index')"
                    class="block py-2 pl-3 pr-4 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent 
                        md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 
                        dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">
                Category
            </Link>
            </li>
        </ul>
      </div>
    </div>
  </nav>

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

