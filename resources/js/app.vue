<script setup>
import { RouterView } from 'vue-router'
import { onMounted } from 'vue'
import axios from 'axios'

onMounted(() => {
  const token = localStorage.getItem('token')
  if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }
})
</script>

<template>
  <RouterView />
</template>