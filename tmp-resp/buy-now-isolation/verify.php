<?php
/** Isolated regression checks against the real Buy Now handler. */
define('ABSPATH', __DIR__);
function dg_is_woocommerce_active() { return true; }
function __( $text, $domain ) { return $text; }
function wc_get_checkout_url() { return '/checkout/'; }
function wc_get_product($id) { return new WC_Product($id); }
function WC() { return $GLOBALS['wc']; }
function dg_clear_session_selection() { WC()->session->set('dg_checkout_selection', null); }
class WC_Product {
 public function __construct(public int $id) {}
 public function get_id(){return $this->id;}
 public function get_type(){return 'simple';}
 public function get_attribute($name){return '';}
}
class TestSession { public array $values=['dg_checkout_selection'=>['old']]; public function set($key,$value){$this->values[$key]=$value;} }
class TestCart {
 public array $cart_contents=['regular'=>['product_id'=>99,'quantity'=>3], 'old'=>['product_id'=>46,'quantity'=>2,'dg_is_buy_now'=>true]];
 public bool $fail=false;
 public function add_to_cart($id,$qty,$variation,$attributes,$data){if($this->fail)return false;$key='buy-'.$id;$this->cart_contents[$key]=array_merge($data,['product_id'=>$id,'quantity'=>($this->cart_contents[$key]['quantity']??0)+$qty]);return $key;}
 public function get_cart(){return $this->cart_contents;}
 public function set_quantity($key,$qty,$refresh=true){$this->cart_contents[$key]['quantity']=$qty;return true;}
 public function set_session(){}
}
require __DIR__.'/../../wp-content/themes/dragon-glow/inc/checkout/class-dg-woocommerce-checkout-handler.php';
$GLOBALS['wc']=(object)['cart'=>new TestCart(),'session'=>new TestSession()];
$handler=new DG_WooCommerce_Checkout_Handler();
$handler->handle(47,1);
$active=array_keys(array_filter(WC()->cart->get_cart(),fn($item)=>!empty($item['dg_is_buy_now'])));
if($active!==['buy-47'])throw new RuntimeException('Only latest Buy Now item must be active: '.json_encode($active));
if(WC()->session->values['dg_checkout_selection']!==null)throw new RuntimeException('Old selection must be cleared');
if(WC()->cart->cart_contents['regular']['quantity']!==3 || WC()->cart->cart_contents['old']['quantity']!==2)throw new RuntimeException('Regular cart must be preserved');
$handler->handle(47,2);
if(WC()->cart->cart_contents['buy-47']['quantity']!==2)throw new RuntimeException('Repeated Buy Now must use requested quantity');
$before=WC()->cart->get_cart(); WC()->cart->fail=true;
$result=$handler->handle(48,1);
if($result['success'] || WC()->cart->get_cart()!==$before)throw new RuntimeException('Failed add must preserve cart');
echo "PASS: only latest product, exact quantity, cleared selection, preserved cart, failed add unchanged\n";
