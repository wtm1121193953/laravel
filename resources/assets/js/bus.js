
/*
 * 自定义bus
 *
 *   redirect：       自定义跳转（传递的参数同router，post为post方式传参）
 *                    示例：bus.post = {username: 'username'}; router.push('/')
 *
 **/
const bus = new Vue({
    data() {
        return {
            post: {}
        }
    },
    methods: {
        set(key, value){
            if(!this.post) this.post = {};
            this.post[key] = value;
            return this;
        },
        get(key){
            if(this.post && this.post[key]){
                return this.post[key];
            }else{
                return {};
            }
        },
        refresh(name){
            router.replace({path: '/refresh', query: {name: name}})
        }
    },
    created() {
        let el = this;

        // 页面刷新相关
        window.onbeforeunload = () => {
            Lockr.set('post_data', el.post)
        }
        let post_data = Lockr.get('post_data')
        if (post_data) {
            el.post = post_data
        }

        el.$on('redirect', function (data) {
            router.push({path: data.url, query: data.query, params: data.params});
            data.post && (el.post = data.post);
        })
    }
});

export default bus;