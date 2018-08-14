<template>
    <page title="编辑商户信息" :breadcrumbs="breadcrumbs">
        <merchant-form
                v-loading="isLoading"
                v-if="merchant"
                :data="merchant"
                :isDraft="isDraft"
                @cancel="cancel"
                @save="doEdit"
                @saveDraft="doEditDraft"
        />
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import MerchantForm from './merchant-form'
    export default {
        name: "merchant-edit",
        components: {
            MerchantForm,
        },
        data() {
            return {
                isLoading: false,
                id: null,
                merchant: null,
                isDraft: false,
                breadcrumbs: {},
            }
        },
        methods: {
            doEdit(data){
                this.isLoading = true;
                if (!this.isDraft){
                    api.post('/merchant/edit', data).then(() => {
                        this.$message.success('保存成功');
                        router.push({
                            path: '/merchants',
                            name: 'MerchantList',
                            params: this.$route.params,
                        });
                        store.commit('setCurrentMenu', '/merchants');
                    }).finally(() => {
                        this.isLoading = false;
                    })
                }else {
                    api.post('/merchant/draft/delete', {id: data.id}).then((res) => {
                        api.post('/merchant/add', data).then(() => {
                            this.$message.success('保存成功');
                            router.push('/merchants');
                        })
                        let menu_copy = Lockr.get('userMenuList');
                        menu_copy[0].sub[4].name = '草稿箱(' + res.count + ')';
                        store.commit('setMenus', menu_copy);
                    }).finally(() => {
                        this.isLoading = false;
                    })
                }
            },
            getDetail(){
                this.isLoading = true;
                let url = '';
                if (this.isDraft) {
                    url = '/merchant/draft/detail';
                }else {
                    url = '/merchant/detail';
                }
                api.get(url , {id: this.id}).then(data => {
                    this.merchant = data;
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            cancel(){
                if (this.isDraft) {
                    router.push('/merchant/drafts');
                }else {
                    router.push('/merchants');
                }
            },
            doEditDraft(data) {
                if (!data.name) {
                    this.$message.error('商户名称不能为空');
                    return false;
                }
                this.isLoading = true;
                api.post('/merchant/draft/edit', data).then(() => {
                    this.$message.success('保存成功');
                    router.push('/merchant/drafts');
                }).finally(() => {
                    this.isLoading = false;
                })
            }
        },
        created(){
            this.id = this.$route.query.id;
            this.isDraft = this.$route.query.type == 'draft-list';
            if (this.isDraft){
                this.breadcrumbs = {'草稿箱': '/merchant/drafts'};
            } else {
                this.breadcrumbs = {'我的商户': '/merchants'}
            }
            if(!this.id){
                this.$message.error('id不能为空');
                if (this.isDraft) {
                    router.push('/merchant/drafts');
                }else {
                    router.push('/merchants');
                }
                return false;
            }
            this.getDetail();
        }
    }
</script>

<style scoped>

</style>