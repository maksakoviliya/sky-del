import ApiService from "../../services/ApiService";
import {getError} from "../../utils/helpers";
import router from "../../router";

export const namespaced = true;

export const state = {
    acts: [],
    actsMeta: {}
};

export const getters = {
    acts: (state) => {
        return state.acts;
    },
    actsMeta: (state) => {
        return state.actsMeta;
    },
}

export const mutations = {
    SET_ACTS(state, acts) {
        state.acts = acts
    },
    SET_ACTS_META(state, actsMeta) {
        state.actsMeta = actsMeta
    },
}

export const actions = {
    async fetchActs({commit}) {
        try {
            commit("SET_LOADING", true, { root: true });
            let response = await ApiService.fetchActs(router.currentRoute.value.query);
            commit("SET_ACTS", response.data.data)
            commit("SET_ACTS_META", response.data.meta);
            return response.data.data;
        } catch (error) {
            console.log("error", error)
            commit("SET_ERROR", getError(error), { root: true });
            throw error
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    async createAct({commit}, params) {
        try {
            commit("SET_LOADING", true, { root: true });
            let response = await ApiService.createAct(params);
            return response.data.data;
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
            throw error
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    async downloadAct({commit}, id) {
        commit("SET_LOADING", true, { root: true });
        try {
            return await ApiService.downloadAct(id);
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
            throw error
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    }
}