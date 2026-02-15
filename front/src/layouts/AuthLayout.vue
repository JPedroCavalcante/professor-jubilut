<script setup>
</script>

<template>
  <div class="auth-layout">
    <div class="auth-bg-blob auth-bg-blob--1" aria-hidden="true"></div>
    <div class="auth-bg-blob auth-bg-blob--2" aria-hidden="true"></div>

    <div class="auth-container">
      <RouterView />
    </div>
  </div>
</template>

<style scoped>
.auth-layout {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0f0f23 0%, #1e1b4b 40%, #312e81 100%);
  padding: 1.5rem;
  position: relative;
  overflow: hidden;
}

.auth-bg-blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.25;
  pointer-events: none;
}

.auth-bg-blob--1 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, #6366f1 0%, #8b5cf6 100%);
  top: -150px;
  right: -150px;
}

.auth-bg-blob--2 {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, #818cf8 0%, #c4b5fd 100%);
  bottom: -100px;
  left: -100px;
}

.auth-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 440px;
}
</style>
