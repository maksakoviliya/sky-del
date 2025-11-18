<template>
  <div class="flex flex-col items-center justify-center h-screen max-h-screen overflow-y-auto bg-zinc-200 px-4">
    <LogoLink />
    <div class="mx-auto mt-5 max-w-sm w-full p-10 rounded-lg bg-white shadow-md">
      <h1 class="font-bold text-lg">Вход</h1>
      <Form
          @submit="onSubmit"
          :validation-schema="schema"
          class="flex flex-col gap-3 mt-4"
      >
        <CommonInput
            name="phone"
            type="text"
            label="Телефон"
            placeholder="Ваш телефон"
        />
        <CommonInput
            name="password"
            type="password"
            label="Пароль"
            placeholder="Your password"
        />

        <button class="submit-btn block px-4 py-2 bg-violet-500 rounded-md text-white font-bold mt-4 hover:bg-violet-600 outline-none" type="submit">Войти</button>
      </Form>
    </div>
  </div>
</template>

<script>
import {Form} from "vee-validate";
import * as Yup from "yup";
import "yup-phone";
import CommonInput from "../../components/common/CommonInput";
import ApiService from "../../services/ApiService";
import {getError} from "../../utils/helpers";
import {useStore} from "vuex";
import {useRouter} from "vue-router";
import LogoLink from "../../components/common/LogoLink";
import * as yup from "yup";

export default {
  name: "Login",

  components: {
    CommonInput,
    Form,
    LogoLink
  },

  setup() {
    const store = useStore()
    const router = useRouter()

    async function onSubmit(values, actions) {
      try {
        await ApiService.login(values);
        const authUser = await store.dispatch("auth/getAuthUser");
        if (authUser) {
          await store.dispatch("auth/setGuest", { value: "isNotGuest" });
          await router.push({ name: 'home'});
        } else {
          const error = Error(
              "Unable to fetch user after login, check your API settings."
          );
          error.name = "Fetch User";
          throw error;
        }
      } catch (error) {
        const errors = getError(error)
        actions.setErrors(errors);
      }
    }

    // Using yup to generate a validation schema
    // https://vee-validate.logaretm.com/v4/guide/validation#validation-schemas-with-yup
    const schema = Yup.object().shape({
      phone: yup.string().required().length(11).matches(/^7\d+$/, 'Не верный формат номера'),
      password: Yup.string().min(6).required(),
    });

    return {
      onSubmit,
      schema,
    };
  },
};
</script>
