<template>
  <ul class="k-commentions-list">
    <slot/>
  </ul>
</template>

<script>
export default {

}
</script>

<style lang="scss">
.k-commentions-list > * + * {
  margin-top: 2px;
}
</style>
