<template>
  <div
    class="k-commentions-text"
    v-if="viewSource"
  >
    <pre class="code"><!--
      --><code class="language-commention">{{ source }}</code><!-- v-html="$options.filters.highlightSource(source)"
    --></pre>
  </div>
  <div
    class="k-commentions-text"
    v-else
    v-html="text"
  />
</template>

<script>

  // import Prism from "../vendors/prism";

  // Prism.languages.commention = Prism.languages.extend("markdown", {});

  // Prism.languages.insertBefore("commention", "prolog", {
  //   "mark": {
  //     pattern: /(<mark>.*<\/mark>)/i,
  //   },
  // });

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

    filters: {
      highlightSource(value) {
        return value;
        // return Prism.highlight(value, Prism.languages.commention, 'commention');
      },
    },
  }

</script>

<style lang="scss">

  .k-commentions-text {
    /**
     * 1. Emulate `line-height: normal`
     */
    > * + * {
      margin-top: 1.14em; /* 1 */
    }

    mark {
      background: #f4d08b;
      border-radius: .25em;
      box-decoration-break: clone;
      margin: -.125em;
      padding: .125em;
    }

    /* Links */

    a:link {
      text-decoration: underline;
    }

    a[rel~="noopener"] {
      padding-inline-end: 2ch;
      position: relative;
    }

    a[rel~="noopener"]::after {
      // background: #fff;
      color: var(--color-text-light);
      content: " ↗️";
      // display: inline-block;
      // font-size: .9em;
      // margin-left: .25em;
      // position: absolute;
      // top: .08em;
      // right: .5ch;
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

    .token.important,
    .token.bold {
      font-weight: bold;
    }

    .token.italic {
      font-style: italic;
    }

    pre,
    code {
      background: var(--color-background);
      font-family: var(--font-family-mono);
      font-size: .8125rem;
    }

    code[class*="language-"],
    pre[class*="language-"] {
      word-spacing: normal;
      word-break: normal;
      word-wrap: normal;
      line-height: 1.4;

      -moz-tab-size: 4;
      -o-tab-size: 4;
      tab-size: 4;

      -webkit-hyphens: none;
      -moz-hyphens: none;
      -ms-hyphens: none;
      hyphens: none;
    }

    pre {
      padding: .5em 1em;
      white-space: pre-wrap;
      width: 100%;
    }

    pre code.language-commention {
      background: transparent;
      display: block;
      white-space: pre-wrap;
      width: 100%;
    }

    /* Code blocks */
    pre[class*="language-"] {
      padding: 1em;
      margin: .5em 0;
      overflow: auto;
    }

    // :not(pre) > code[class*="language-"],
    // pre[class*="language-"] {
    //   background: #f5f2f0;
    // }

    /* Inline code */
    :not(pre) > code[class*="language-"] {
      padding: .1em;
      border-radius: .3em;
      white-space: normal;
    }

    .token.url,
    .token.punctuation,
    .token.comment,
    .token.prolog,
    .token.doctype,
    .token.cdata,
    .token.tag {
      color: var(--color-text-light);
    }

    .token.url .token.content,
    .token.title .token.punctuation {
      color: var(--color-text);
    }

    .token.code.keyword,
    .token.mark {
      box-decoration-break: clone;
      padding: .125em;
      margin: -.125em;
      border-radius: .25em;
    }

    .token.mark {
      background: #f4d08b;
    }

    // .token.regex,
    // .token.important,
    // .token.variable {
    //   color: #e90;
    // }

    .token.code:not(.keyword) {
      background: rgba(#16171a, .07);
      color: var(--color-text);
      display: block;
      margin-bottom: -1.4em;
      margin: -.375em;
      padding: .375em;
    }

    .token.code.keyword {
      background: rgba(#16171a, .07);
    }

    .token.strike {
      text-decoration: line-through;
    }

    // .token.entity {
    //   cursor: help;
    // }
  }

</style>
