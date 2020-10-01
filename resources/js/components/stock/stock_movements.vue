<template>
  <div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Movimientos de Stock</h4>
            <table class="mt-3 table" v-if="items.length > 0">
              <thead>
                <tr>
                  <td>Fecha</td>
                  <td>Desde</td>
                  <td>Hacia</td>
                  <td>Cantidad de Pares</td>
                  <td>Comentario</td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="i in items" :key="'mov-'+i.id">
                  <td>{{ getDateFormat(i.created_at) }}</td>
                  <td>{{ i.sucursal_from }}</td>
                  <td>{{ i.sucursal_to }}</td>
                  <td>{{ i.qty }}</td>
                  <td>{{ i.description }}</td>
                  <td>
                    <a class="btn btn-sm btn-outline-primary" target="_blank" :href="'/stock/movements/print/'+i.id">Imprimir</a>
                    <a class="btn btn-sm btn-outline-primary" :href="'/stock/movements/'+i.id">Ver detalle</a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <nav v-if="items && items.length > 0">
            <ul class="pagination pg-blue">
                <li class="page-item"><a class="page-link" @click="getData(prev_page)">Anterior</a></li>
                <li class="page-item"><a class="page-link" @click="getData(next_page)">Siguiente</a></li>
                <li class="page-item"><a class="page-link" @click="getData(last_page)">Última página</a></li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-md-12">
        <div class="card mb-4 wow fadeIn">
          <div class="card-body d-sm-flex justify-content-between">
            <h4 class="mb-2 mb-sm-0 pt-1">
              <span>Agregar transferencia</span>
            </h4>
          </div>
        </div>

        <div class="form-row mb-4">
          <div class="col">
              <input type="text" class="z-depth-1 form-control"  v-model="barcode" placeholder="Código de barras" @change="getDetailItem">
          </div>
          <div class="col">
              <sucursal-selector @updatesucursal="updateSucursal($event, 'sucursal_from')" label="Desde" :options="sucursals"></sucursal-selector>
          </div>
          <div class="col">
              <sucursal-selector @updatesucursal="updateSucursal($event, 'sucursal_to')" label="Hasta" :options="sucursals"></sucursal-selector>
          </div>
        </div>

        <table class="table z-depth-1 w-100 white" v-if="detailItems.length > 0">
          <thead>
              <tr>
                <th scope="col">Marca</th>
                <th scope="col">Artículo</th>
                <th scope="col">Color</th>
                <th scope="col">Número</th>
                <th v-for="s in sucursals" :key="s+Math.random()" scope="col">Stock {{ s.name }}</th>
                <th scope="col">Cantidad</th>
              </tr>
          </thead>
          <tbody>
              <stock-movement-item v-for="i in detailItems" :key="i.id + Math.random()" :item="i" :sucursals="sucursals"> </stock-movement-item>
          </tbody>
        </table>
        <div v-if="detailItems.length > 0 && sucursal_from && sucursal_to">
            <div class="form-group">
              <label>Comentarios</label>
              <textarea class="form-control rounded-0" rows="3" v-model="description"></textarea>
            </div>
            <button type="button" class="btn btn-primary" @click="activeConfirmDialog = true">
              Confirmar
            </button>
        </div>
      </div>
    </div>

    <md-dialog-confirm
      :md-active.sync="activeConfirmDialog"
      :md-title="'¿Seguro querés confirmar movimientos de stock? ('+qty+' pares)'"
      md-confirm-text="Dale mecha"
      md-cancel-text="Cancelar"
      @md-confirm="confirmSubmit" />

      <md-dialog-alert
      :md-active.sync="alertActive"
      :md-title="alert_title"
      :md-content="alert_content" />

      <md-dialog-confirm
      :md-active.sync="alertActivePrint"
      :md-title="alert_title"
      :md-content="alert_content"
      @md-confirm="print"
      md-confirm-text="Imprimir"
      md-cancel-text="Cancelar"
       />
  </div>
</template>

<script>
  export default {
    name: 'StockMovements',
    data() {
      return {
        items: [],
        sucursals: [],
        activeConfirmDialog: false,
        alertActivePrint: false,
        alertActive: false,
        alert_title: '',
        alert_content: '',
        alertActivePrint: false,

        prev_page: null,
        next_page: null, 
        last_page: null,

        id_to_print: null,

        sucursal_from: null,
        sucursal_to: null,
        description: null,
        qty: 0,
        barcode: null,
        detailItems: []
      }
    },
    props: {
        
    },
    methods: {
      updateSucursal(value, property){
        this[property] = value
      },
      getDetailItem() {
        if (this.barcode != null) {
            axios.get('/api/stock/detail_item_barcode', {params: { barcode: this.barcode }})
            .then(response => {
              let data = response.data
              this.barcode = null
              if (data.item.length === 0) {
                this.alertActive = true;
                this.alert_title =  data.title
                this.alert_content = data.message
                return
              }
              this.qty += 1
              // check if article already have one movement
              var index = 0
              this.detailItems.forEach(i => {
                if (i.id === data.item.id){
                  i.qty += 1
                  return
                }    
                index++;
              });
              if (index === this.detailItems.length) {
                data.item.qty = 1
                this.detailItems.unshift(data.item)
              }
            });
        }
      },
      getDateFormat(date) {
        date = new Date(date)
        return date.getDate() + "/" + (date.getMonth()+1) + "/" + date.getFullYear()
      },
      getData(url){
        let endpoint = url == null ? '/api/stock/movements' : url
        axios.get(endpoint).then(response => {
          this.items = response.data.data
          this.next_page = response.data.next_page_url
          this.prev_page = response.data.prev_page_url
          this.last_page = response.data.last_page_url
        });
      },
      getSucursals() {
        axios.get('/api/sucursals').then(response => {
            this.sucursals = response.data;
        });
      },
      confirmSubmit() {
        axios.post('/api/stock/movements', {items: this.detailItems.map(item => {
                                                        return {
                                                          id: item.id, 
                                                          qty: item.qty,
                                                        }
                                                      }),
                                            description: this.description,
                                            from: this.sucursal_from, 
                                            to: this.sucursal_to,
                                            qty: this.qty })
        .then(response => {
          this.alert_title = response.data.status
          this.alert_content = response.data.message
          this.alertActivePrint = true
          this.id_to_print = response.data.id_movement

          if (response.data.statusCode === 200) {
            this.items.push(this.appendNewMovement(response.data.id_movement))
            this.detailItems = []
          }
        })
      },
      appendNewMovement(id) {
        return {
          qty: this.qty,
          sucursal_from: this.getSucursalName(this.sucursa_from),
          sucursal_to: this.getSucursalName(this.sucursal_to),
          id: id
        }
      },
      getSucursalName(id) {
        for (var i = 0; i < this.sucursals.length; i++)
          if (this.sucursals[i].id == id)
            return this.sucursals[i].name
      },
      print() {
        if (this.id_to_print) {
          window.open(
            '/stock/movements/print/'+this.id_to_print,
            '_blank' 
          );
        }
      }
    },
    created() {
        this.getData();
        this.getSucursals();
    }
  }
</script>