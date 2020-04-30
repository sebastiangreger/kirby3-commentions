<template>
  <section class="k-commentions-section k-section">

    <!-- <k-info-field
      label="⚠️ Missing dependencies"
      text="Some dependencies are missing"
      theme="negative">
    </k-info-field> -->

    <header class="k-section-header">
      <k-headline>{{ headline }}</k-headline>
      <k-button-group>
        <k-button icon="code" :theme="viewSource ? 'active' : ''" @click="toggleViewSource()">View source</k-button>
        <k-button icon="refresh" @click="refresh">Refresh</k-button>
      </k-button-group>
    </header>

    <k-box theme="negative" v-if="error">
      <k-text size="small" v-if="error == 'version'">
        <strong>Action required!</strong> You updated the <em>Kirby3-Commentions</em> plugin to version 1.x, but your setup is still in the (now incompatible) 0.x format! Worry not: no data has been lost, but you will have to use the <a href="/commentions-migrationassistant" target="_blank">Migration assistant</a> to get things running again!
      </k-text>
      <k-text size="small" v-else>
        {{ error }}
      </k-text>
    </k-box>

    <commentions-table>
      <thead>
        <th class="k-commentions-column-status">Status</th>
        <th class="k-commenttions-column-author">Author</th>
        <th class="k-commentions-column-text">Kommentar</th>
        <th/>
      </thead>
      <tbody>
        <table-item
          v-for="item in commentions"
          :key="item.uid"
          :item="item"
          :view-source="viewSource"
        >
        </table-item>
      </tbody>
    </table>
    <!-- <k-list>
      <k-list-item v-for="(value, key) in commentions"
        v-bind:icon="value[3]"
        v-bind:class="value[2]"
        v-bind:options="value[1]"
        v-bind:text="value[0]"
        info="INFO"
        @action="action" />
    </k-list> -->

    <k-empty
      v-if="commentions === null || commentions.length == 0"
      layout="list"
      icon="chat"
      @click="refresh"
    >
      {{ empty }}
    </k-empty>

    <!-- <k-commentions-remove-dialog ref="remove" @success="fetch" /> -->

  </section>

</template>

<script>

  // import CommentionsRemoveDialog from './CommentionRemoveDialog.vue';
  import TableItem from "./TableItem.vue";

  import CommentionsTable from "./CommentionsTable.vue";

  export default {

    extends: "k-info-section",

    components: {
      // 'k-commentions-remove-dialog': CommentionsRemoveDialog,
      'table-item': TableItem,
      'commentions-table': CommentionsTable,
    },

    props: {
      // Re-defined from Kirby’s section mixing, because otherwise
      // the section would throw an error, when hot-reloaded during
      // development.
      name: String,
      parent: String
    },

    data() {
      return {
        headline: null,
        commentions: null,
        empty: null,
        error: null,
        viewSource: false,
      }
    },

    created() {
      this.load().then((response) => {
        this.headline    = response.headline;
        this.commentions = response.commentions;
        this.empty       = response.empty;
        this.error       = response.error;
      });
    },


    methods: {
        // re-defining load method from Kirby’s section mixin, because
        // it’s not included otherwhise when hot-reloading the module,
        // which leads to an error during development.
        load() {
          return this.$api.get(this.parent + '/sections/' + this.name);
        },

        toggleViewSource() {
          this.viewSource = !this.viewSource;
        },

        action(type) {

          // distill action and commentid from action
          var re = /^([a-z]+)-(\w{10})\|(.*?)$/;
          var array = re.exec(type);
          var action = array[1];
          var commentid = array[2];
          var pageid = array[3];

          // call the api for the desired action
          switch(action) {
            case 'open':
              window.open( pageid, "_blank" )
              break;
            // for deletion, display a verification popup
            case 'delete':
              if (confirm("Really delete? This can not be undone!") == true) {
                this.callapi( commentid + '/' + pageid, 'delete' );
              }
              break;
            // hand all other actions directly to the api
            default:
              this.callapi( commentid + '/' + pageid, action );
          }

        },

        async callapi( filename, task ) {
          const endpoint = "commentions/" + task + "/" + filename;
          const response = await this.$api.get( endpoint );
          this.load().then(response => {
            this.commentions    = response.commentions;
          });
        },

        refresh() {
          this.load().then(response => {
            this.commentions    = response.commentions;
          });
        }

      },

  };

</script>

<style lang="scss">
.k-commentions-section .k-button[data-theme="active"] {
  position: relative;

  &::before {
    background: rgba(#000, .1);
    border-radius: .25rem;
    bottom: .6rem;
    content: "";
    left: .375rem;
    position: absolute;
    right: .375rem;
    top: .7rem;
  }
}

/* overriding the component's default styles to accommodate commentions */
// .k-section-name-commentions { font-size:.75rem; line-height:1.25rem; }
// .k-section-name-commentions .k-list-item { align-items:start; }
// .k-section-name-commentions .k-list-item-content { align-items:start; }
// .k-section-name-commentions .k-list-item-image .k-icon svg { opacity:1; }
// .k-section-name-commentions .k-list-item-commention-approved .k-list-item-image .k-icon svg { color:#a7bd68; }
// .k-section-name-commentions .k-list-item-commention-unapproved .k-list-item-image .k-icon svg { color:#d16464; }
// .k-section-name-commentions .k-list-item-commention-pending .k-list-item-image .k-icon svg { color:#d16464; }
// .k-section-name-commentions .k-list-item-commention-update .k-list-item-image .k-icon svg { color:#d16464; }
// .k-section-name-commentions .k-list-item-text { white-space:pre-line; }
// .k-section-name-commentions .k-list-item-text small { opacity:0; }

// .k-commentions-preview-source {
//   background: var(--color-background);
// }

</style>
