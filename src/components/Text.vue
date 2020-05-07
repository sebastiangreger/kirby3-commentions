<template>
  <div
    class="k-commentions-text"
    v-if="viewSource"
  >
    <pre class="code"><code class="language-commention">{{ source }}</code></pre>
  </div>
  <div
    class="k-commentions-text"
    ref="preview"
    v-else
    v-html="text"
  />
</template>

<script>

export default {
  props: {
    text: {
      type: String,
      default: "",
    },
    source: {
      type: String,
      default: "",
    },
    viewSource: {
      type: Boolean,
      default: false,
    }
  },

  data() {
    return [];
  },

  mounted() {
    this.postfixPreviewHTML();
  },

  updated() {
    this.postfixPreviewHTML();
  },

  methods: {
    postfixPreviewHTML(container) {
      if (!this.$refs.preview) {
        return;
      }

      // Add target="_blank" to all links.
      const links = this.$refs.preview.querySelectorAll("a");
      links.forEach(element => {
        if (element.getAttribute("target") !== "_blank") {
          element.setAttribute("target", "_blank");
        }
      });
    },
  }
};
</script>

<style lang="scss">
.k-commentions-text {
  /**
   * 1. [NO BREAK SPACE] + [NORTH EAST ARROW]
   * 2. Prevents the underline from being rendered below the external
   *    link icon.
   */
  a[rel~="noopener"]::after {
    color: #999;
    content: "\00a0\2197\fe0e"; /* 1 */
    display: inline-block; /* 2 */
  }

  /**
    * 1. Emulate `line-height: normal`
    */
  > * + * {
    margin-top: 1.14em; /* 1 */
  }

  /**
    * 1. Half of `line-height: normal`
    */
  li + li {
    margin-top: .57em; /* 1 */
  }

  blockquote {
    border-inline-start: 2px solid var(--color-border);
    padding-inline-start: calc(1em - 2px);
  }

  /* Lists */

  ul,
  ol {
    margin-inline-start: 1.5em;
  }

  ul,
  ul > li {
    list-style: disc;
  }

  ol,
  ol > li {
    list-style: decimal;
  }

  pre,
  code {
    background: var(--color-background);
    border-radius: 1px;
    font-family: var(--font-family-mono);
    font-size: .8125rem;
  }

  code[class*="language-"],
  pre[class*="language-"] {
    hyphens: none;
    line-height: 1.4;
    tab-size: 4;
    word-break: normal;
    word-spacing: normal;
    word-wrap: normal;
  }

  pre {
    border: 1px solid #ddd;
    padding: .5em 1em;
    white-space: pre-wrap;
    width: 100%;
  }

  :not(pre) > code {
    border: 1px solid #e5e5e5;
    border-radius: 2px;
    box-decoration-break: clone;
    font-size: 1em;
    line-height: inherit;
    margin: -1px -2px;
    padding: 0 1px;
    white-space: normal;
  }

  mark {
    background: #F9DC91;
    border-radius: 2px;
    box-decoration-break: clone;
    margin: -1px -2px;
    padding: 1px 2px;
  }
}
</style>
