import ApiService from "../../services/ApiService";
import {getError} from "../../utils/helpers";
import router from '../../router'

export const namespaced = true;

export const state = {
    orders: [],
    recipients: [],
    selectedOrders: [],
    ordersMeta: {},
    ordersAnalytics: {},
};

export const getters = {
    recipients: (state) => {
        return state.recipients;
    },
    orders: (state) => {
        return state.orders;
    },
    selectedOrders: (state) => {
        return state.selectedOrders;
    },
    ordersMeta: (state) => {
        return state.ordersMeta;
    },
    ordersAnalytics: (state) => {
        return state.ordersAnalytics;
    },
}

export const mutations = {
    SET_ORDERS(state, orders) {
        state.orders = orders
    },
    SET_RECIPIENTS(state, recipients) {
        state.recipients = recipients
    },
    SET_SELECTED_ORDERS(state, selectedOrders) {
        state.selectedOrders = selectedOrders
    },
    SET_ORDERS_META(state, ordersMeta) {
        state.ordersMeta = ordersMeta
    },
    SET_ORDERS_ANALYTICS(state, ordersAnalytics) {
        state.ordersAnalytics = ordersAnalytics
    },
}

export const actions = {
    async fetchRecipients({commit}) {
        commit("SET_LOADING", true, { root: true });
        try {
            let response = await ApiService.fetchRecipients();
            commit("SET_RECIPIENTS", response.data.data);
            return response.data.data;
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
            throw error
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    async fetchRecipientsForUser({commit}, id) {
        commit("SET_LOADING", true, { root: true });
        try {
            let response = await ApiService.fetchRecipientsForUser(id);
            commit("SET_RECIPIENTS", response.data.data);
            return response.data.data;
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
            throw error
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    async fetchOrder({commit}, id) {
        commit("SET_LOADING", true, { root: true });
        try {
            let response = await ApiService.fetchOrder(id);
            return response.data.data;
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
            throw error
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    async fetchOrders({commit}) {
        commit("SET_LOADING", true, { root: true });
        try {
            let response = await ApiService.fetchOrders(router.currentRoute.value.query);
            console.log('response', response.data.analytics)
            commit("SET_ORDERS", response.data.data);
            commit("SET_ORDERS_META", response.data.meta);
            commit("SET_ORDERS_ANALYTICS", response.data.analytics);
            return response.data.data;
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
            throw error
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    async setOrderStatus({commit}, params) {
        commit("SET_LOADING", true, { root: true });
        try {
            let response = await ApiService.setOrderStatus(params.order_id , params);
            return response.data.data;
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
            throw error
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    async setOrderPayment({commit}, params) {
        commit("SET_LOADING", true, { root: true });
        try {
            let response = await ApiService.setOrderPayment(params.order_id , params);
            return response.data.data;
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
            throw error
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    async ordersPay({commit}, params) {
        commit("SET_LOADING", true, { root: true });
        try {
            let response = await ApiService.ordersPay(params);
            return response.data.data;
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
            throw error
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    async setOrderCourier({commit}, params) {
        commit("SET_LOADING", true, { root: true });
        try {
            let response = await ApiService.setOrderCourier(params.order_id , params);
            return response.data.data;
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
            throw error
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    selectOrder({commit, state}, order) {
        let selected = state.selectedOrders
        let index = selected.findIndex(item => item.id === order.id)
        if (index >= 0) {
            selected.splice(index, 1)
        } else {
            selected.push(order)
        }
        commit('SET_SELECTED_ORDERS', selected)
    },
    async fetchOrdersAnalytics({commit}) {
        commit("SET_LOADING", true, { root: true });
        try {
            let response = await ApiService.fetchOrdersAnalytics();
            return response.data.data;
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
            throw error
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    },
    async exportOrders({commit}, orders) {
        commit("SET_LOADING", true, { root: true });
        try {
            return await ApiService.exportSelectedOrders(orders.map((order) => order.id));
        } catch (error) {
            commit("SET_ERROR", getError(error), { root: true });
            throw error
        } finally {
            commit("SET_LOADING", false, { root: true });
        }
    }
}