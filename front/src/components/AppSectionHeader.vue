<template>
  <q-card flat bordered class="app-section-header">
    <q-card-section class="row items-start justify-between q-col-gutter-md app-section-header__content">
      <div class="col-12 col-md">
        <div class="row items-center q-gutter-sm">
          <q-avatar rounded size="42px" class="app-section-header__icon">
            <q-icon :name="icon" size="22px" />
          </q-avatar>
          <div>
            <div class="text-subtitle1 text-weight-bold">{{ title }}</div>
            <div v-if="subtitle" class="text-caption text-grey-7">{{ subtitle }}</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-auto">
        <div class="row justify-start justify-md-end q-gutter-sm app-section-header__actions">
          <slot name="actions" />
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: 'sym_r_health_and_safety'
  }
})
</script>

<style scoped>
.app-section-header__content {
  width: 100%;
}

.app-section-header__actions {
  width: 100%;
}

@media (max-width: 599px) {
  .app-section-header__actions {
    justify-content: stretch;
  }

  .app-section-header__actions :deep(.q-btn) {
    width: 100%;
  }
}
</style>
