<template>
  <div>
      <p>{{ label }}</p>
      <ul>
        <li>
          <input @keydown.tab="disableTab" type="text" v-model="search" @keyup.enter="findUser">
        </li>
        <li>
            <span>{{ user.toUpperCase() }}</span>
        </li>
      </ul>
  </div>
</template>
)
<script>
  export default {
    name: 'UserSelector',
    data() { 
      return {
        user: '',
        options: [],
        search: ''
      }
    },
    props: {
      required: {
          type: Boolean,
          default: true
      },
      label: {
          type: String,
          default: ''
      },
      role: {
          type: String, 
          default: ''
      }
    },
    methods: {
        disableTab(e) {
          if (this.user == '')
            e.preventDefault()
        }, 
        updateUser(user) {
            this.$emit('update', user)
        },
        getData() {
            axios.get('/api/get_users', this.role != "" ? {params: { role: this.role }} : null)
            .then(response => {
                console.log(response.data)
                this.options = response.data;
            });
        },
        findUser() {
          this.options.forEach(user => {
            if (user.id == this.search) {
              this.user = user.name
              this.updateUser(user)
              return;
            }
          })
        }
    },
    created() {
        this.getData()
    }
  }
</script>

<style lang="scss" scoped>
  ul {
    list-style: none;
    padding: 0
  }
  input {
    max-width: 40%
  }
  ul li{
    display: inline;
  }
  span {
    margin-left: 1rem;
    font-size: 1.4rem;
  }
</style>