import ApiService from "../../services/ApiService";
import {getError} from "../../utils/helpers";
import {notify} from "@kyvg/vue3-notification";


export const namespaced = true;

export const state = {
    clients: [],
};

export const getters = {
    clients: (state) => {
        return state.clients;
    },
}

export const mutations = {
    SET_CLIENTS(state, clients) {
        state.clients = clients;
    },
}

export const actions = {
    async fetchClients({commit}) {
        commit("SET_LOADING", true, { root: true });
        try {
            const response = await ApiService.getClients();
            commit("SET_CLIENTS", response.data.data);
            return response.data.data;
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    async fetchClient({commit}, id) {
        commit("SET_LOADING", true, { root: true });
        try {
            const response = await ApiService.getClient(id);
            return response.data.data;
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    async fetchAllClients({commit}) {
        commit("SET_LOADING", true, { root: true });
        try {
            const response = await ApiService.fetchAllClients();
            // return response.data.data;
            return response.data;
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    async exportOrders({commit}, params) {
        commit("SET_LOADING", true, { root: true });
        try {
            return await ApiService.exportOrders(params);
        } catch (error) {
            notify({
                type: 'error',
                title: 'Ошибка',
                text: getError(error)
            });
            commit("SET_ERROR", getError(error), { root: true });
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    }
}