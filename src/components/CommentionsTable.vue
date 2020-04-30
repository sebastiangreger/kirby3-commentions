<template>
  <table class="k-commentions-table">
    <slot />
  </table>
</template>

<script>
  export default {

  }
</script>

<style lang="scss">

  $structure-item-height: 38px;

  .k-commentions-table {
    background: #fff;
    border-spacing: 0;
    box-shadow: var(--box-shadow-item);
    font-size: var(--font-size-small);
    position: relative;
    table-layout: fixed;
    width: 100%;

    th,
    td {
      border-bottom: 1px solid var(--color-background);
      border-inline-end: 1px solid var(--color-background);
      line-height: 1.25em;
      overflow: hidden;
      padding: .75rem .5rem;
      text-overflow: ellipsis;
    }

    th {
      background: #fff;
      color: var(--color-text-light);
      font-weight: 400;
      height: $structure-item-height;
      left: 0;
      padding: 0 .75rem;
      position: sticky;
      right: 0;
      top: 0;
      width: 100%;
      z-index: 1;

      [dir="ltr"] & {
        text-align: left;
      }

      [dir="rtl"] & {
        text-align: right;
      }
    }

    th:last-child,
    td:last-child {
      border-inline-end: 0;
      padding: 0;
      width: $structure-item-height;
    }

    td {
      vertical-align: top;
    }

    tr:last-child td {
      border-bottom: 0;
    }

    .k-commentions-column-status,
    .k-commenttions-column-author {
      width: percentage(1 / 6);
    }

    .k-commentions-column-text {
      width: auto;
    }
  }

</style>
