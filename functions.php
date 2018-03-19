<?php 
/*开启缩列图功能*/
add_theme_support('post-thumbnails');

/**
 * WordPress 添加额外选项字段到常规设置页面
 * https://www.wpdaxue.com/add-field-to-general-settings-page.html
 */
$new_general_setting = new new_general_setting();
class new_general_setting {
	function new_general_setting( ) {
		add_filter( 'admin_init' , array( &$this , 'register_fields' ) );
	}
	function register_fields() {
		//添加注册租
		register_setting( 'general', 'gszt');//增加公司主题
		register_setting( 'general', 'gsms');//增加公司描述
		register_setting( 'general', 'gsjj');//增加简介
		register_setting( 'general', 'gsdh');//增加人事电话号码
		register_setting( 'general', 'gsdhyw');//增加业务电话
		register_setting( 'general', 'gsdz');//增加公司地址
		register_setting( 'general', 'gsmail');//增加公司邮箱
		register_setting( 'general', 'gsqq');//增加公司qq
		
		//添加注册项
		add_settings_field('fav_color0', '<label for="gszt">'.__('公司主题' ).'</label>' , array(&$this, 'gszt_html') , 'general' );
		
		add_settings_field('fav_colora0', '<label for="gsms">'.__('主题描述' ).'</label>' , array(&$this, 'gsms_html') , 'general' );
		
		add_settings_field('fav_color', '<label for="gsjj">'.__('公司简介' ).'</label>' , array(&$this, 'fields_html') , 'general' );
		
		add_settings_field('fav_color1', '<label for="gsdh">'.__('公司电话' ).'</label>' , array(&$this, 'gsdh_html') , 'general' );
		
		add_settings_field('fav_colora1', '<label for="gsdhyw">'.__('业务电话' ).'</label>' , array(&$this, 'gsdhyw_html') , 'general' );
		
		add_settings_field('fav_color2', '<label for="gsdz">'.__('公司地址' ).'</label>' , array(&$this, 'gsdz_html') , 'general' );
		
		add_settings_field('fav_color3', '<label for="gsmail">'.__('公司邮箱' ).'</label>' , array(&$this, 'gsmail_html') , 'general' );
	
		add_settings_field('fav_colora2', '<label for="gsqq">'.__('公司qq' ).'</label>' , array(&$this, 'gsqq_html') , 'general' );
		}
		
	//返回回调函数
	function gszt_html(){
		$value_zt = get_option( 'gszt', '' );
		echo '<input id="gszt" name="gszt" value="'.$value_zt.'" size="80">';			
	}
	
	function gsms_html(){
		$value_ms = get_option( 'gsms', '' );
		echo '<textarea id="gsms" name="gsms" rows="5" cols="100">'. $value_ms.'</textarea>';				
	}
	
	function fields_html() {
		$value = get_option( 'gsjj', '' );
		echo '<textarea id="gsjj" name="gsjj" rows="8" cols="100">'. $value.'</textarea>';		
	}
	
	function gsdh_html() {
		$value_dh = get_option( 'gsdh', '' );
		echo '<input id="gsdh" name="gsdh" value="'.$value_dh.'">';		
	}
	
	function gsdhyw_html() {
		$value_dhyw = get_option( 'gsdhyw', '' );
		echo '<input id="gsdhyw" name="gsdhyw" value="'.$value_dhyw.'">';		
	}
	
	function gsdz_html() {
		$value_dz = get_option( 'gsdz', '' );
		echo '<input id="gsdz" name="gsdz" value="'.$value_dz.'" size="120">';		
	}
	function gsmail_html() {
		$value_gsmail = get_option( 'gsmail', '' );
		echo '<input id="gsmail" name="gsmail" value="'.$value_gsmail.'" size="80">';		
	}
	
	function gsqq_html() {
		$value_gsqq = get_option( 'gsqq', '' );
		echo '<input id="gsqq" name="gsqq" value="'.$value_gsqq.'">';		
	}
}




/**
 * WordPress 添加面包屑导航 
 * https://www.wpdaxue.com/wordpress-add-a-breadcrumb.html
 */
function cmp_breadcrumbs() {
	$delimiter = '»'; // 分隔符
	$before = '<span class="current">'; // 在当前链接前插入
	$after = '</span>'; // 在当前链接后插入
	if ( !is_home() && !is_front_page() || is_paged() ) {
		echo '<div id="crumbs">'.__( '当前位置:' , 'cmp' );
		global $post;
		$homeLink = home_url();
		echo ' <a itemprop="breadcrumb" href="' . $homeLink . '">' . __( '首页' , 'cmp' ) . '</a> ' . $delimiter . ' ';
		if ( is_category() ) { // 分类 存档
			global $wp_query;
			$cat_obj = $wp_query->get_queried_object();
			$thisCat = $cat_obj->term_id;
			$thisCat = get_category($thisCat);
			$parentCat = get_category($thisCat->parent);
			if ($thisCat->parent != 0){
				$cat_code = get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' ');
				echo $cat_code = str_replace ('<a','<a itemprop="breadcrumb"', $cat_code );
			}
			echo $before . '' . single_cat_title('', false) . '' . $after;
		} elseif ( is_day() ) { // 天 存档
			echo '<a itemprop="breadcrumb" href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
			echo '<a itemprop="breadcrumb"  href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
			echo $before . get_the_time('d') . $after;
		} elseif ( is_month() ) { // 月 存档
			echo '<a itemprop="breadcrumb" href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
			echo $before . get_the_time('F') . $after;
		} elseif ( is_year() ) { // 年 存档
			echo $before . get_the_time('Y') . $after;
		} elseif ( is_single() && !is_attachment() ) { // 文章
			if ( get_post_type() != 'post' ) { // 自定义文章类型
				$post_type = get_post_type_object(get_post_type());
				$slug = $post_type->rewrite;
				echo '<a itemprop="breadcrumb" href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a> ' . $delimiter . ' ';
				echo $before . get_the_title() . $after;
			} else { // 文章 post
				$cat = get_the_category(); $cat = $cat[0];
				$cat_code = get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
				echo $cat_code = str_replace ('<a','<a itemprop="breadcrumb"', $cat_code );
				echo $before . get_the_title() . $after;
			}
		} elseif ( !is_single() && !is_page() && get_post_type() != 'post' ) {
			$post_type = get_post_type_object(get_post_type());
			echo $before . $post_type->labels->singular_name . $after;
		} elseif ( is_attachment() ) { // 附件
			$parent = get_post($post->post_parent);
			$cat = get_the_category($parent->ID); $cat = $cat[0];
			echo '<a itemprop="breadcrumb" href="' . get_permalink($parent) . '">' . $parent->post_title . '</a> ' . $delimiter . ' ';
			echo $before . get_the_title() . $after;
		} elseif ( is_page() && !$post->post_parent ) { // 页面
			echo $before . get_the_title() . $after;
		} elseif ( is_page() && $post->post_parent ) { // 父级页面
			$parent_id  = $post->post_parent;
			$breadcrumbs = array();
			while ($parent_id) {
				$page = get_page($parent_id);
				$breadcrumbs[] = '<a itemprop="breadcrumb" href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
				$parent_id  = $page->post_parent;
			}
			$breadcrumbs = array_reverse($breadcrumbs);
			foreach ($breadcrumbs as $crumb) echo $crumb . ' ' . $delimiter . ' ';
			echo $before . get_the_title() . $after;
		} elseif ( is_search() ) { // 搜索结果
			echo $before ;
			printf( __( 'Search Results for: %s', 'cmp' ),  get_search_query() );
			echo  $after;
		} elseif ( is_tag() ) { //标签 存档
			echo $before ;
			printf( __( 'Tag Archives: %s', 'cmp' ), single_tag_title( '', false ) );
			echo  $after;
		} elseif ( is_author() ) { // 作者存档
			global $author;
			$userdata = get_userdata($author);
			echo $before ;
			printf( __( 'Author Archives: %s', 'cmp' ),  $userdata->display_name );
			echo  $after;
		} elseif ( is_404() ) { // 404 页面
			echo $before;
			_e( 'Not Found', 'cmp' );
			echo  $after;
		}
		if ( get_query_var('paged') ) { // 分页
			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() )
				echo sprintf( __( '( Page %s )', 'cmp' ), get_query_var('paged') );
		}
		echo '</div>';
	}
}
?>
<?php 
/*公司项目图片与介绍*/
/*参数：$p  文章的id*/
function programContainer($p){
	?>
	<a href="<?php echo get_permalink($p);?>">
		<div class="program-container-tu">							
			<img src="<?php $img_src = wp_get_attachment_image_src( get_post_thumbnail_id( $p ), "Full"); echo $img_src[0]; ?>" class="img-responsive">
								
		</div>
		<div class="program-container-js">
			<?php echo get_post_meta($p,"container-1-jj",$single = true); ?>
		</div>
	</a>
	<?php	
}
?>
<?php 
/**
* 不同分类使用不同的文章模板
* From https://www.wpdaxue.com/custom-single-post-template.html
*/
//定义模板文件所在目录为 single 文件夹
define(SINGLE_PATH, TEMPLATEPATH . '/single');
//自动选择模板的函数
function wpdaxue_single_template($single) {
	global $wp_query, $post;
	//通过分类别名或ID选择模板文件
	foreach((array)get_the_category() as $cat) :
		$substr_cat = substr($cat->slug,0,4);
		echo $substr_cat;
		if(file_exists(SINGLE_PATH . '/single-cat-' . $substr_cat . '.php')){
			return SINGLE_PATH . '/single-cat-' . $substr_cat . '.php';
		}
		else{
			return SINGLE_PATH . '/single-cat-common.php';
		}
	endforeach;
}
add_filter('single_template', 'wpdaxue_single_template');

//通过 category_template 钩子挂载函数
define(CATEGORY_PATH, TEMPLATEPATH . '/category');
//自动选择模板的函数
function wpdaxue_category_template($category) {
	global $wp_query,$category;
	//通过分类别名或ID选择模板文件
	if(is_category()){
		$cat = get_query_var('cat');//如果用get_the_category只能获取到文章对应的分类别名，在分类页中只能用get_query_var()
		$theCatGetC = get_category($cat);
		$theCatGetSlug = $theCatGetC->slug;
		//echo $theCatGet;
		echo $theCatGetSlug;
		$substr_cat = substr($theCatGetSlug,0,4);
		echo $substr_cat;
		echo CATEGORY_PATH . '/category-cat-' . $substr_cat . '.php';
		if(file_exists(CATEGORY_PATH . '/category-cat-' . $substr_cat . '.php'))
			{
			return CATEGORY_PATH . '/category-cat-' . $substr_cat . '.php';
			}
		else{
			return CATEGORY_PATH . '/category-cat-common.php' ;
		}
	}
}
//通过 category_template 钩子挂载分类列表的函数
add_filter('category_template', 'wpdaxue_category_template');
?>
<?php 
//分类页文章页页面导航
function navShow(){
	$theCat = get_query_var('cat');
	$getCat = get_category($theCat);
	echo $getCat->term_id;
	$catId = $getCat->term_id;
	//$catArry = get_the_category();
	//$catId = $catArry[0]->term_id;
		//活动当前分类的父分类id，如果父分类为0时，属于当前分类的顶级分类目录
	//$faterCatArrId = $catArry[0]->category_parent;//category_parent为获取分类的父类id
	$faterCatArrId = $getCat->category_parent;
	echo "父类：".$faterCatArrId."<br/>";
	if($faterCatArrId !=0){				
		$theCatArr = array(
			'child_of' => $faterCatArrId,
		);
	}
	else{
		echo $catId;
		$theCatArr = array(
			'child_of' => $catId,
		);
	}
	$faterCatgories = get_categories($theCatArr);
	foreach($faterCatgories as $tcategory){
		$navLi = '<li><a href="'.get_category_link($tcategory->term_id).'">'.$tcategory->name.'</a></li>';
		echo $navLi;
	}
}
?>
<?php 
//单页面栏目导航
function XmPageNavShow($xStr){
	$xArray = explode(',',$xStr);
	print_r($xArray);
	$a = count($xArray);
	$b = 0;
	echo $a;
	while($b<=$a){
		echo '<li><a href="'.get_permalink($xArray[$b]).'">'.$xArray[$b+1].'</a></li>';
		$b=$b+2;
	}
}

//单页面导航调用2
function childPageNav($x,$y){
	echo "当前id：".$x;
	echo "父类id：".$y;
	$pages = get_pages('child_of='.$x.'&sort_column=post_date&sort_order=desc&parent='.$x);
	if($pages !=null){
		foreach($pages as $page){
			echo '<li><a href="'.get_page_link($page->ID).'">'.$page->post_title.'</a></li>';
		}
	}
	else{
		echo "不是数组";
		$zPages = get_pages('child_of='.$y.'&sort_column=post_date&sort_order=asc&parent='.$y);
		echo $post->post_parent;
		foreach($zPages as $zPage){
			echo '<li><a href="'.get_page_link($zPage->ID).'">'.$zPage->post_title.'</a></li>';
		}
	}
}	
?>
<?php
class Ludou_Tax_Image{  
    function __construct(){  
          
        // 新建分类页面添加自定义字段输入框  
        add_action( 'category_add_form_fields', array( $this, 'add_tax_image_field' ) );  
        // 编辑分类页面添加自定义字段输入框  
        add_action( 'category_edit_form_fields', array( $this, 'edit_tax_image_field' ) );  
  
        // 保存自定义字段数据  
        add_action( 'edited_category', array( $this, 'save_tax_meta' ), 10, 2 );  
        add_action( 'create_category', array( $this, 'save_tax_meta' ), 10, 2 );       
    } // __construct  
   
    /** 
     * 新建分类页面添加自定义字段输入框 
     */  
    public function add_tax_image_field(){  
    ?>  
        <div class="form-field">  
            <label for="term_meta[tax_image]">分类封面</label>  
            <input type="text" name="term_meta[tax_image]" id="term_meta[tax_image]" value="" />  
            <p class="description">输入分类封面图片URL</p>  
        </div><!-- /.form-field -->  
          
        <!-- TODO: 在这里追加其他自定义字段表单，如： -->  
          
        <!--  
        <div class="form-field">  
            <label for="term_meta[tax_keywords]">分类关键字</label>  
            <input type="text" name="term_meta[tax_keywords]" id="term_meta[tax_keywords]" value="" />  
            <p class="description">输入分类关键字</p>  
        </div>  
        -->  
    <?php  
    } // add_tax_image_field  
   
    /** 
     * 编辑分类页面添加自定义字段输入框 
     * 
     * @uses get_option()       从option表中获取option数据 
     * @uses esc_url()          确保字符串是url 
     */  
    public function edit_tax_image_field( $term ){  
          
        // $term_id 是当前分类的id  
        $term_id = $term->term_id;  
          
        // 获取已保存的option  
        $term_meta = get_option( "ludou_taxonomy_$term_id" );  
        // option是一个二维数组  
        $image = $term_meta['tax_image'] ? $term_meta['tax_image'] : '';  
          
        /** 
         *   TODO: 在这里追加获取其他自定义字段值，如： 
         *   $keywords = $term_meta['tax_keywords'] ? $term_meta['tax_keywords'] : ''; 
         */  
    ?>  
        <tr class="form-field">  
            <th scope="row">  
                <label for="term_meta[tax_image]">分类封面</label>  
                <td>  
                    <input type="text" name="term_meta[tax_image]" id="term_meta[tax_image]" value="<?php echo esc_url( $image ); ?>" />  
                    <p class="description">输入分类封面图片URL</p>  
                </td>  
            </th>  
        </tr><!-- /.form-field -->  
          
        <!-- TODO: 在这里追加其他自定义字段表单，如： -->  
          
        <!--  
        <tr class="form-field">  
            <th scope="row">  
                <label for="term_meta[tax_keywords]">分类关键字</label>  
                <td>  
                    <input type="text" name="term_meta[tax_keywords]" id="term_meta[tax_keywords]" value="<?php echo $keywords; ?>" />  
                    <p class="description">输入分类关键字</p>  
                </td>  
            </th>  
        </tr>  
        -->  
          
    <?php  
    } // edit_tax_image_field  
   
    /** 
     * 保存自定义字段的数据 
     * 
     * @uses get_option()      从option表中获取option数据 
     * @uses update_option()   更新option数据，如果没有就新建option 
     */  
    public function save_tax_meta( $term_id ){  
   
        if ( isset( $_POST['term_meta'] ) ) {  
              
            // $term_id 是当前分类的id  
            $t_id = $term_id;  
            $term_meta = array();  
              
            // 获取表单传过来的POST数据，POST数组一定要做过滤  
            $term_meta['tax_image'] = isset ( $_POST['term_meta']['tax_image'] ) ? esc_url( $_POST['term_meta']['tax_image'] ) : '';  
  
            /** 
             *   TODO: 在这里追加获取其他自定义字段表单的值，如： 
             *   $term_meta['tax_keywords'] = isset ( $_POST['term_meta']['tax_keywords'] ) ? $_POST['term_meta']['tax_keywords'] : ''; 
             */  
  
            // 保存option数组  
            update_option( "ludou_taxonomy_$t_id", $term_meta );  
   
        } // if isset( $_POST['term_meta'] )  
    } // save_tax_meta  
   
} // Ludou_Tax_Image    
$wptt_tax_image = new Ludou_Tax_Image();  


/*添加后台自定义菜单以及其子菜单*/
function add_diy_menu() {
 
    add_menu_page( '微信公众号', '微信公众号', 'administrator', 'custompage', 'rainbow_my_function_menu', '', 100);
 
    add_submenu_page('custompage', '子菜单1', '设置关键词回复', 'administrator', 'custompage', 'rainbow_my_function_menu1');
 
    add_submenu_page('custompage', '子菜单2', '模式设置', 'administrator', 'your-admin-sub-menu2', 'my_function_submenu2');
	add_submenu_page('custompage', '子菜单3', '首页slider', 'administrator', 'your-admin-sub-menu3', 'my_function_submenu3');
 
}
add_action('admin_menu','add_diy_menu');
 
function rainbow_my_function_menu() {
    echo "<h2>基本设置</h2>";
} 
 
function rainbow_my_function_menu1() {
    echo "<h2>设置微信关键词以及相关回复</h2>";
	echo '
		<div class="key_form">
			<form action="../wx/wx-keyword-add.php" method="post">
				<p>关键词  ：<input type="text" name="theKeyWord"></p>
				<p>对应内容：<textarea rows="6" cols="180" name="keyWordMs"></textarea></p>
				<input type="submit" value="提交">
			</form>
		</div>
	';
}
function my_function_submenu2() { 
    echo "<h2>模式设置</h2>";
}
function my_function_submenu3() { 
    echo "<h2>首页slider</h2>";
}