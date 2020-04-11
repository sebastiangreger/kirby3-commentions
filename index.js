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
								type: 'user',
								back: 'white',
							}"
							:options="[
								{icon: 'check', text: 'Approve', click: 'approve'},
								{icon: 'trash', text: 'Delete', click: 'delete'}
							]"
							v-bind:info="key"
							v-bind:text="value"
							@action="action"
						/>
					</k-list>	
				</section>
				
			`,
			methods: {
				
				action(type) {
					// TODO: this is a dirty, dirty hack :( need to figure out the official way to hand over the key to this method
					var re = /.*(\d{10}\.json).*/;
					var array = re.exec(event.target.parentElement.parentElement.parentElement.parentElement.innerText);
					var filename = array[0];
					switch(type) {
						case 'approve':
							this.callapi( filename, 'approve' );
						break;
						case 'delete':
							if (confirm("Really delete?") == true) {
								this.callapi( filename, 'delete' );
							}
						break;
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
				
			}	
					
		},
		
		commentions2: {

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
