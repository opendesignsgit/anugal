<?php
class Paulund_Wp_List_Table{
    public function __construct($name,$post_type,$taxonomy,$dashicon){	
		$this->name = $name;
		$this->post_type = $post_type;
		$this->taxonomy = $taxonomy;
		$this->dashicon = $dashicon;
        add_action( 'admin_menu', array($this, 'add_menu_example_list_table_page' ));
    }

    public function add_menu_example_list_table_page(){
        add_menu_page( $this->name, $this->name, 'manage_options', $this->name.'_group', array($this, 'list_table_page'),$this->dashicon,10 );
		add_submenu_page($this->name.'_group',$this->name.' Group',$this->name.' Group','manage_options',$this->name.'_group',array($this, 'list_table_page'),0);
    }

    public function list_table_page(){
        $exampleListTable = new Example_List_Table($this->post_type,$this->taxonomy);
        $exampleListTable->prepare_items();
        ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <h2><?php echo $this->name ?> Group</h2>
				<div class="add_button" style="margin-top: 2%;">
				<span id="fusion-split-page-title-action" class="fusion-split-page-title-action">
					<a style="padding: 1%;border: 1px solid;text-decoration: none;" href="<?php echo site_url()?>/wp-admin/edit-tags.php?taxonomy=<?php echo $this->taxonomy ?>&post_type=<?php echo $this->post_type ?>">Add New Group</a>
				</span>
				</div>
                <?php $exampleListTable->display(); ?>
            </div>
        <?php
    }
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Example_List_Table extends WP_List_Table{
	function __construct($post_type,$taxonomy){
		parent::__construct( array(
			'post_type'  => $post_type,
			'taxonomy'  => $taxonomy,
		) );        
	}
    public function prepare_items(){
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 50;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
		
    }

    public function get_columns()
    {
        $columns = array(
            //'id'          => 'ID',
            'title'       => 'Title',
            'count'      => 'Count',
            'actions'      => 'Actions',
        );

        return $columns;
    }

    public function get_hidden_columns()
    {
        return array();
    }

    public function get_sortable_columns()
    {
        return array('title' => array('title', false));
    }

    private function table_data()
    {
        $data = array();
		$post_type = $this->_args['post_type'];
		$taxonomy = $this->_args['taxonomy'];
		$terms = get_terms([
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
		]);
		$i = 0;
		foreach ($terms as $term){
			$data[$i]['title'] = '<a href="edit.php?'.$taxonomy.'='.$term->slug.'&post_type='.$post_type.'">'.$term->name.'</a>';
			$data[$i]['count'] = $term->count;
			$data[$i]['actions'] = '<a href="javascript:void(0)" class="duplicate_group" data-post_type="'.$post_type.'" data-taxonomy="'.$taxonomy.'" data-term_id="'.$term->term_id.'">Duplicate</a> <a href="'.site_url().'/wp-admin/term.php?taxonomy='.$taxonomy.'&tag_ID='.$term->term_id.'&post_type='.$post_type.'" class="edit_group" data-taxonomy="'.$taxonomy.'" data-term_id="'.$term->term_id.'">Edit</a> <a href="javascript:void(0)" class="delete_group" style="color:red" data-post_type="'.$post_type.'" data-taxonomy="'.$taxonomy.'" data-term_id="'.$term->term_id.'">Delete</a>';
			$i++;
		}

        return $data;
    }

    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            //case 'id':
            case 'title':
            case 'count':
                return $item[ $column_name ];
			case 'actions':
				return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }

    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'title';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}

add_action('wp_ajax_delete_group', 'delete_group'); 
function delete_group(){
	$term_id = $_REQUEST['term_id'];
	$taxonomy = $_REQUEST['taxonomy'];
	$post_type = $_REQUEST['post_type'];
	$myposts = get_posts(array(
		'showposts' => -1,
		'post_type' => $post_type,
		'tax_query' => array(
			array(
			'taxonomy' => $taxonomy,
			'field' => 'id',
			'terms' => $term_id
		))
	));
	$i = 0;
	$result = array();
	foreach ($myposts as $mypost) {
		$result[$i] = wp_trash_post($mypost->ID);
		$i++;
	}
	wp_delete_term( $term_id, $taxonomy);
	print_r($result);
	die();
}