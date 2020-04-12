panel.plugin('sgkirby/commentions', {

	sections: {
		
		commentions: {

			data: function () {

				return {
					headline: null,
					commentions: null
				}

			},

			created: function() {

				this.load().then(response => {
					this.headline 		= response.headline;
					this.commentions    = response.commentions;
				});
			},

			template: `

				<section class="k-commentions-section k-section">
					<header class="k-section-header">
						<k-headline>{{ headline }}</k-headline>
						<k-button-group>
							<k-button icon="refresh" @click="refresh">Refresh</k-button>
						</k-button-group>
					</header>
					<k-list>
						<k-list-item
							v-for="(value, key) in commentions"
							:icon="{
								type: 'chat',
								back: 'white',
							}"
							v-bind:options="value[1]"
							v-bind:info="key"
							v-bind:text="value[0]"
							@action="action"
						/>
					</k-list>
				</section>

			`,

			methods: {

				action(type) {

					// distill action and commentid from action
					var re = /^([a-z]+)-(\d{10})\|(.*?)$/;
					var array = re.exec(type);
					var action = array[1];
					var commentid = array[2];
					var pageid = array[3];
					console.log(array);

					// call the api for the desired action
					switch(action) {
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

		},

	}

});
