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
                isReEdit: null,
                merchant: null,
                isDraft: false,
                dataType:'',
                breadcrumbs: {},
            }
        },
        methods: {
            doEdit(data){
                this.isLoading = true;
                if (!this.isDraft){
                    data.dataType = this.dataType;
                    api.post('/cs/merchant/edit', data).then(() => {
                        this.$confirm('您的商户资料已提交, 请耐心等待工作人员审核! ', '温馨提示', {
                            confirmButtonText: '好的',
                            cancelButtonText: '返回我的商户',
                        }).then(() => {
                            this.$menu.change('/cs/merchant/audit/list')
                        }).catch(() => {
                            router.push('/cs/merchants');
                        })
                        // this.$message.success('保存成功');
                        // router.push('/cs/merchant/audit/list');
                        /*if(this.isReEdit){
                            router.push('/cs/merchant/audit/list');
                        }else{
                            router.push('/cs/merchants');
                        }*/

                    }).finally(() => {
                        this.isLoading = false;
                    })
                }else {
                    api.post('/cs/merchant/draft/delete', {id: data.id}).then((res) => {
                        api.post('/cs/merchant/add', data).then(() => {
                            this.$message.success('保存成功');
                            router.push('/cs/merchants');
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
                    url = '/cs/merchant/draft/detail';
                }else {
                    url = '/cs/merchant/detail';
                }
                api.get(url , {id: this.id, isReEdit: this.isReEdit}).then(data => {
                    this.merchant = data;
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            cancel(){
                if (this.isDraft) {
                    router.push('/cs/merchant/drafts');
                }else {
                    if(this.isReEdit){
                        router.push('/cs/merchant/audit/list');
                    }else{
                        router.push('/cs/merchants');
                    }
                }
            },
            doEditDraft(data) {
                if (!data.name) {
                    this.$message.error('商户名称不能为空');
                    return false;
                }
                this.isLoading = true;
                api.post('/cs/merchant/draft/edit', data).then(() => {
                    this.$message.success('保存成功');
                    router.push('/cs/merchant/drafts');
                }).finally(() => {
                    this.isLoading = false;
                })
            }
        },
        created(){
            this.id = this.$route.query.id;
            this.isReEdit = this.$route.query.type == 'cs-merchant-reedit';
            this.isDraft = this.$route.query.type == 'draft-list';
            switch (this.$route.query.type) {
                // 用于判断判断数据属于哪个表
                case 'merchant-list':
                    this.dataType = 'csMerchant';
                    break;
                case 'cs-merchant-reedit':
                    this.dataType = 'audit';
                    break;
            }
            if (this.isDraft){
                this.breadcrumbs = {'草稿箱': '/cs/merchant/drafts'};
            } else {
                if (this.$route.query.hasOwnProperty('isPilot') && this.$route.query.isPilot) {
                    this.breadcrumbs = {'我的试点商户': '/cs/merchant/pilots'}
                } else {
                    this.breadcrumbs = {'我的超市商户': '/cs/merchants'}
                }
            }
            if(!this.id){
                this.$message.error('id不能为空');
                if (this.isDraft) {
                    router.push('/cs/merchant/drafts');
                }else {
                    router.push('/cs/merchants');
                }
                return false;
            }
            this.getDetail();
        }
    }
</script>

<style scoped>

</style>