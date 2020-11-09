<template>
  <div>
      <p>{{ label }}</p>
      <ul>
        <li>
          <input @keydown.tab="disableTab" type="text" v-model="search" @keyup.enter="findUser">
        </li>
        <li>
            <span v-if="user">{{ user.name.toUpperCase() }}</span>
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
        options: [],
        search: '',
        user: this.actualUser
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
      },
      actualUser:  {
          type: Object,
          default: null
      }
    },
    watch: {
      actualUser(newValue) {
        this.user = newValue
        if (newValue != null) {
          this.search = ''
        }
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
            axios.get('/api/users', this.role != "" ? {params: { role: this.role }} : null)
            .then(response => {
                console.log(response.data)
                this.options = response.data;
            });
        },
        findUser() {
          var found = false
          this.options.forEach(user => {
            if (user.id == this.search) {
              this.username = user.name
              found = true
              this.user = user
              this.updateUser(user)
              return;
            }
          })
          
          if (!found) {
            this.user = null
            this.updateUser(null)
          }
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