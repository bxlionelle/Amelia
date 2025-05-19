<script setup>
import { onMounted } from 'vue'
import { initFlowbite } from 'flowbite'
import Header from './Header.vue';
import Footer from './Footer.vue';
import Hero from './Hero.vue';

// Accept balance as a prop
defineProps({
  balance: {
    type: Number,
    default: 0
  }
})

// initialize components based on data attribute selectors
onMounted(() => {
    initFlowbite();
})



</script>
<template>
    <!-- header -->
    <Header></Header>
    <!-- end -->
    <!-- main content -->
    
    <slot />
    <!-- end -->
    <!-- footer -->

    <Footer></Footer>

    <!-- end -->
</template>
