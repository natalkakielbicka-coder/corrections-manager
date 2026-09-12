<template>
  <header class="app-header">
    <div class="app-header__inner">
      <RouterLink class="app-header__logo" to="/"> Manager poprawek </RouterLink>

      <nav class="app-header__nav" aria-label="Główna nawigacja">
        <RouterLink to="/">Poprawki</RouterLink>
      </nav>
    </div>
  </header>
</template>

<style scoped>
.app-header {
  border-bottom: 1px solid #e5e7eb;
  background-color: #ffffff;
}

.app-header__inner {
  display: flex;
  min-height: 72px;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
  width: min(100% - 32px, 1280px);
  margin-inline: auto;
}

.app-header__logo {
  color: #111827;
  font-size: 20px;
  font-weight: 700;
  text-decoration: none;
}

.app-header__nav {
  display: flex;
  align-items: center;
  gap: 24px;
}

.app-header__nav a {
  color: #4b5563;
  font-weight: 600;
  text-decoration: none;
}

.app-header__nav a:hover,
.app-header__nav a.router-link-active {
  color: #2563eb;
}
</style>
