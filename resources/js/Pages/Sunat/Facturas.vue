<template>
  <app-layout>
    <template #header>
      <div class="grid grid-cols-1 md:grid-cols-10">
        <div class="col-span-9">
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Facturas
          </h2>
        </div>        
      </div>
    </template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div
                class="
                  py-2
                  align-middle
                  inline-block
                  min-w-full
                  sm:px-6
                  lg:px-8
                "
              >
                <div
                  class="
                    shadow
                    overflow-hidden
                    border-b border-gray-200
                    sm:rounded-lg
                  "
                >
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          ID VENTA
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          CLIENTE
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          VENDEDOR
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          TIPO COMPROBANTE
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          NUM. COMPROBANTE
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          ESTADO
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          OPCIÓN
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr
                        v-for="factura in facturas"
                        :key="factura.id"
                      >
                        <td class="py-3 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                {{ factura.id }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="py-3 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                {{ factura.cliente.nombre }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="py-3 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                {{ factura.vendedor.usuario }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="py-3 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                {{ factura.tipo_comprobante }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="py-3 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                {{ factura.serie_comprobante }} - {{ factura.num_comprobante }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="py-3 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                {{ factura.estado }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="py-3 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                <!-- <button v-if=" factura.estado === 'No emitido'" class="btn btn-blue" @click.once="enviarFactura(factura)" data-modal-toggle="popup-modal"> -->
                                <button v-if=" factura.estado === 'No emitido'" @click="modal = {isOpen: true, question: `enviar a Sunat la factura ${factura.serie_comprobante}-${factura.num_comprobante}`, func: this.enviarFactura, fact: factura}" class="btn btn-blue">
                                  <file-upload-outline></file-upload-outline>
                                </button>
                                <button v-if=" factura.estado === 'Enviada'" @click="modal = {isOpen: true, question: `enviar comunicación de baja a Sunat de la factura ${factura.serie_comprobante}-${factura.num_comprobante}`, func: this.comunicarBaja, fact: factura}" class="btn btn-red">
                                  <delete-forever></delete-forever>
                                </button>
                                <button v-if=" factura.estado === 'Baja en proceso'" @click="modal = {isOpen: true, question: `consultar el ticket de la factura ${factura.serie_comprobante}-${factura.num_comprobante}`, func: this.consultarTicket, fact: factura}" class="btn btn-yellow">
                                  <eye-check></eye-check>
                                </button>
                                <!-- <jet-nav-link :href="route('sunat.enviar', factura.id)">
                                    Enviar
                                </jet-nav-link> -->
                              </div>
                            </div>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
  <!-- Delete Product Modal -->
  <div v-show="modal.isOpen" class="fixed inset-0 w-full h-screen flex items-center justify-center bg-semi-75 bg-gray-700 bg-opacity-50">
      <div class="relative px-4 w-full max-w-md h-full md:h-auto">
          <!-- Modal content -->
          <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
              <!-- Modal header -->
              <div class="flex justify-end p-2">
                  <button type="button" @click="modal.isOpen = false" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                  </button>
              </div>
              <!-- Modal body -->
              <div class="p-6 pt-0 text-center">
                  <svg class="mx-auto mb-4 w-14 h-14 text-gray-400 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Esta seguro de {{modal.question}}?</h3>
                  <button @click.once="modal.func(modal.fact)" :key="buttonKey" type="button" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                      Si, estoy seguro!
                  </button>
                  <button @click="modal.isOpen = false" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:ring-gray-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600">No, cancelar</button>
              </div>
          </div>
      </div>
  </div>
  <div v-show="toast.isOpen" class="animate__animated animate__fadeInDown fixed inset-y-0 w-full h-screen flex items-start justify-center">
    <div id="toast-success" class="flex items-center p-4 mb-4 w-full max-w-xs text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800">
      <div v-show="!toast.error" class="inline-flex flex-shrink-0 justify-center items-center w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
      </div>
      <div v-show="toast.error" class="inline-flex flex-shrink-0 justify-center items-center w-8 h-8 text-red-500 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
      </div>
      <div class="ml-3 text-sm font-normal">{{toast.message}}</div>
      <!-- <button type="button" @click="toast = {isOpen: false}" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-collapse-toggle="toast-success" aria-label="Close">
          <span class="sr-only">Close</span>
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
      </button> -->
    </div>
  </div>
</template>

<script>
const axios = require("axios");
import AppLayout from "@/Layouts/AppLayout";
import JetNavLink from '@/Jetstream/NavLink.vue'
import { DeleteForever, FileUploadOutline, EyeCheck } from 'mdue';
import 'animate.css';

export default {
  name: "sunat.facturas",
  props: ["facturas"],
  components: {
    AppLayout,
    JetNavLink,
    DeleteForever,
    FileUploadOutline,
    EyeCheck
  },
  data() {
    return {
      modal: {
        isOpen: false,
        question: '',
        func: '',
        fact: ''
      },
      toast: {
        isOpen: false,
        code: '',
        message: '',
        error: ''
      },
      buttonKey: 1,

      // app_url: this.$root.app_url,


    };
  },
  created() {

  },
  watch: {
    toast: function(val) {
      val.isOpen = setTimeout(() => {
        this.toast.isOpen = false
      }, 4000);
    }
  },
  methods: {
    enviarFactura(factura) {
      this.modal.isOpen = false;
      axios.post(`/sunat/enviar_factura/${factura.id}`)
        .then(response => {
          let error = false;
          if (response.data.error == false) {
            factura.estado = 'Enviada'
            console.log(response.data);
          } else {
            error = true;
            console.log(response.data);
          }
          this.toast = {
            error,
            message: response.data.message,
            isOpen: true
          }
        })
        .catch(function(error) {
          console.log(error);
        });
      setTimeout(() => {
        this.buttonKey++
      }, 1000);
    },
    comunicarBaja(factura) {
      this.modal.isOpen = false;
      axios.post(`/sunat/comunicar_baja/${factura.id}`)
        .then(response => {
          let error = false;
          let message;
          if (response.data.error == false) {
            factura.estado = 'Baja en proceso'
            message = `Se obtuvo el Ticket: ${response.data.ticket}`;
            console.log(response.data);
          } else {
            error = true;
            message = response.data.message;
            console.log(response.data);
          }
          this.toast = {
            error,
            message,
            isOpen: true
          }
        })
        .catch(function(error) {
          console.log(error);
        });
      setTimeout(() => {
        this.buttonKey++
      }, 1000);
    },
    consultarTicket(factura) {    
      this.modal.isOpen = false;
      axios.post(`/sunat/consultar_ticket/${factura.id}`)
        .then(response => {
          let error = false;
          if (response.data.error == false) {
            factura.estado = 'Baja aceptada'
            console.log(response.data);
          } else {
            error = true;
            console.log(response.data);
          }
          this.toast = {
            error,
            message: response.data.message,
            isOpen: true
          }
        })
        .catch(function(error) {
          console.log(error);
        });
      setTimeout(() => {
        this.buttonKey++
      }, 1000);
    }
  }
};
</script>
