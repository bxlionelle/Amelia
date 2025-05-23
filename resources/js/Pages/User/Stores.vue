<script setup>
import { Link, router } from '@inertiajs/vue3'
import { defineProps, computed } from 'vue'

const logout = () => {
    router.post(route('logout'))
}

const props = defineProps({
  products: Array,
  auth: Object,
  cart: Object,
  canLogin: Boolean,
  canRegister: Boolean
})

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
  return Object.values(grouped).sort((a, b) => b.totalValue - a.totalValue)

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
            <Link :href="auth?.user && auth.user.is_admin == 1 ? route('admin.products.index') : route('stores.view')"
                    class="block py-2 pl-3 pr-4 text-blue-700 font-medium rounded hover:bg-gray-100 md:hover:bg-transparent 
                        md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 
                        dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">
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

  <!-- Main Content -->
  <div class="max-w-7xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">Admin Store Products</h1>

    <!-- Check if there are any products -->
    <div v-if="!products || products.length === 0" class="text-center py-12">
      <div class="max-w-md mx-auto">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No products available</h3>
        <p class="mt-1 text-sm text-gray-500">No admin has uploaded any products yet.</p>
      </div>
    </div>

    <!-- Products grouped by admin -->
    <div v-else class="space-y-8">
      <div v-for="adminGroup in productsByAdmin" :key="adminGroup.adminName" class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Admin Header -->
        <div class="bg-gray-50 px-6 py-4 border-b">
          <div class="flex justify-between items-center">
            <!-- Check if products.index route exists before rendering Link -->
            <Link v-if="routeExists('products.index')" 
                  :href="route('products.index')" 
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium hover:underline">
              View All Products →
            </Link>
            <span v-else class="text-gray-400 text-sm">View All Products →</span>
          </div>
        </div>

        <!-- Products Grid -->
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="product in adminGroup.products" :key="product.id" 
                 class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200">
              
              <!-- Product Image -->
            <div class="aspect-w-16 aspect-h-9 bg-gray-200">
              <img
                v-if="product.product_images && product.product_images.length > 0"
                :src="`/storage/${product.product_images[0].image}`"
                :alt="product.imageAlt || 'Product image'"
                class="h-full w-full object-cover object-center lg:h-full lg:w-full"
              />
              <img
                v-else
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/65/No-Image-Placeholder.svg/330px-No-Image-Placeholder.svg.png"
                :alt="product.imageAlt || 'No image available'"
                class="h-full w-full object-cover object-center lg:h-full lg:w-full"
              />
            </div>

              <!-- Product Info -->
              <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                  <h3 class="font-semibold text-lg text-gray-900 line-clamp-1">{{ product.title }}</h3>
                  <span :class="`px-2 py-1 rounded-full text-xs font-medium ${getStockStatus(product).class}`">
                    {{ getStockStatus(product).status }}
                  </span>
                </div>

                <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                  {{ truncateDescription(product.description) }}
                </p>

                <div class="flex justify-between items-center mb-3">
                  <span class="text-2xl font-bold text-gray-900">{{ formatPrice(product.price) }}</span>
                  <span class="text-sm text-gray-500">
                    Stock: {{ getStockStatus(product).quantity }}
                  </span>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-2">
                  <!-- Check if products.show route exists before rendering Link -->
                  <Link v-if="routeExists('products.show')"
                        :href="route('products.show', product.id)" 
                        class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 transition-colors duration-200 text-sm font-medium">
                    View Details
                  </Link>
                  <button v-else
                          @click="console.log('Product details:', product)"
                          class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors duration-200 text-sm font-medium">
                    View Details
                  </button>
                  
                  <button v-if="getStockStatus(product).quantity > 0"
                          @click="console.log('Add to cart:', product)"
                          class="flex-1 bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors duration-200 text-sm font-medium">
                    Add to Cart
                  </button>
                  <button v-else
                          disabled
                          class="flex-1 bg-gray-400 text-white py-2 px-4 rounded-md cursor-not-allowed text-sm font-medium">
                    Out of Stock
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>