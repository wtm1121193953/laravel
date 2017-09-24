import React, {Component} from 'react';

import {Layout, Menu} from 'element-react';
import Header from './components/Header';
import Footer from './components/Footer';
import LeftMenu from './components/LeftMenu';
import Content from './components/Content';


class Home extends Component {
    constructor(props){
        super(props);
    }
    componentDidMount(){
        // todo 判断是否登录, 未登录跳转到登录页面
    }
    onSelect(index){
        console.log(e);
    }
    render(){
        return (<div>
            <Layout.Row>
                <Header/>
            </Layout.Row>
            <Layout.Row>
                <Layout.Col span="2" style={{minWidth: '200px'}}>
                    <LeftMenu/>
                </Layout.Col>
                <Layout.Col span="21" style={{margin: '20px'}}>
                    <Content/>
                </Layout.Col>
            </Layout.Row>
            <Layout.Row>
                <Footer/>
            </Layout.Row>
        </div>);
    }
}

export default Home;