<template>
  <div class="k-grid k-commentions-pagesettings">
    <slot/>
  </div>
</template>

<script>
export default {

}
</script>

<style lang="scss">
.k-commentions-pagesettings {
  margin-top: .5rem;
}
@media screen and (min-width:45em) {
  .k-commentions-pagesettings {
    grid-template-columns: repeat(var(--columns),1fr);
  }
  .k-column[data-width="1/3"], .k-column[data-width="2/6"] {
    grid-column-start: span 4;
  }
}
.k-commentions-pagesettings .k-input[data-theme="field"] {
  border: none;
  background: transparent;
}
</style>
