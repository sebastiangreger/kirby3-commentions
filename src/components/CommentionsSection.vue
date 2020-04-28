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
          <k-list>
            <k-list-item
              v-for="(value, key) in commentions"
              v-bind:icon="value[3]"
              v-bind:class="value[2]"
              v-bind:options="value[1]"
              v-bind:text="value[0]"
              @action="action"
            />
          </k-list>
          <k-empty
            v-if="commentions === null || commentions.length == 0"
            layout="list"
            icon="chat"
            @click="refresh"
          >
            {{ empty }}
          </k-empty>
        </section>

</template>

<script>

  export default {

    data: function () {

      return {
        headline: null,
        commentions: null,
        empty: null,
        error: null
      }

    },

    created: function() {

      this.load().then(response => {
        this.headline     = response.headline;
        this.commentions    = response.commentions;
        this.empty        = response.empty;
        this.error        = response.error;
      });
    },

    methods: {

        action(type) {

          // distill action and commentid from action
          var re = /^([a-z]+)-(\w{10})\|(.*?)$/;
          var array = re.exec(type);
          var action = array[1];
          var commentid = array[2];
          var pageid = array[3];
          console.log(array);

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

/* overriding the component's default styles to accommodate commentions */
.k-section-name-commentions { font-size:.75rem; line-height:1.25rem; }
.k-section-name-commentions .k-list-item { align-items:start; }
.k-section-name-commentions .k-list-item-content { align-items:start; }
.k-section-name-commentions .k-list-item-image .k-icon svg { opacity:1; }
.k-section-name-commentions .k-list-item-commention-approved .k-list-item-image .k-icon svg { color:#a7bd68; }
.k-section-name-commentions .k-list-item-commention-unapproved .k-list-item-image .k-icon svg { color:#d16464; }
.k-section-name-commentions .k-list-item-commention-pending .k-list-item-image .k-icon svg { color:#d16464; }
.k-section-name-commentions .k-list-item-commention-update .k-list-item-image .k-icon svg { color:#d16464; }
.k-section-name-commentions .k-list-item-text { white-space:pre-line; }
.k-section-name-commentions .k-list-item-text small { opacity:0; }

</style>
