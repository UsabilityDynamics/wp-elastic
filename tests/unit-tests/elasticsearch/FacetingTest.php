<?php
namespace elasticsearch;

class FacetingTest extends BaseTestCase
{
	public function testPort()
	{
		$url = Faceting::urlAdd('http://site.com:8080/my/sub/page/', 'facet', 'facet1');
		$this->assertEquals('http://site.com:8080/my/sub/page/?es%5Bfacet%5D%5Band%5D%5B0%5D=facet1', $url);
	}

	public function testUrlAddEmpty()
	{
		$url = Faceting::urlAdd('http://site.com/my/sub/page/', 'facet', 'facet1');
		$this->assertEquals('http://site.com/my/sub/page/?es%5Bfacet%5D%5Band%5D%5B0%5D=facet1', $url);

		$url = Faceting::urlAdd('http://site.com/my/sub/page/', 'facet', 'facet1', 'or');
		$this->assertEquals('http://site.com/my/sub/page/?es%5Bfacet%5D%5Bor%5D%5B0%5D=facet1', $url);
	}

	public function testUrlAddAnother()
	{
		$_GET = array( 'es' => array(
			'facet' => array(
				'and' => array('facet1')
			)
		));

		$url = Faceting::urlAdd('http://site.com/my/sub/page/', 'facet', 'facet2');
		$this->assertEquals('http://site.com/my/sub/page/?es%5Bfacet%5D%5Band%5D%5B0%5D=facet1&es%5Bfacet%5D%5Band%5D%5B1%5D=facet2', $url);

		$url = Faceting::urlAdd('http://site.com/my/sub/page/', 'facet', 'facet2', 'or');
		$this->assertEquals('http://site.com/my/sub/page/?es%5Bfacet%5D%5Band%5D%5B0%5D=facet1&es%5Bfacet%5D%5Band%5D%5B1%5D=facet2', $url);
	}

	public function testUrlAddOther()
	{
		$_GET = array( 'es' => array(
			'field' => array(
				'and' => array('val')
			)
		));

		$url = Faceting::urlAdd('http://site.com/my/sub/page/', 'facet', 'facet2');
		$this->assertEquals('http://site.com/my/sub/page/?es%5Bfield%5D%5Band%5D%5B0%5D=val&es%5Bfacet%5D%5Band%5D%5B0%5D=facet2', $url);

		$url = Faceting::urlAdd('http://site.com/my/sub/page/', 'facet', 'facet2', 'or');
		$this->assertEquals('http://site.com/my/sub/page/?es%5Bfield%5D%5Band%5D%5B0%5D=val&es%5Bfacet%5D%5Bor%5D%5B0%5D=facet2', $url);
	}

	public function testUrlRemoveEmpty()
	{
		$url = Faceting::urlRemove('http://site.com/my/sub/page/', 'facet', 'facet1');
		$this->assertEquals('http://site.com/my/sub/page/', $url);

		$url = Faceting::urlRemove('http://site.com/my/sub/page/', 'facet', 'facet1', 'or');
		$this->assertEquals('http://site.com/my/sub/page/', $url);
	}

	public function testUrlRemoveExisting()
	{
		$_GET = array( 'es' => array(
			'facet' => array(
				'and' => array('facet1')
			)
		));

		$url = Faceting::urlRemove('http://site.com/my/sub/page/', 'facet', 'facet2');
		$this->assertEquals('http://site.com/my/sub/page/?es%5Bfacet%5D%5Band%5D%5B0%5D=facet1', $url);

		$url = Faceting::urlRemove('http://site.com/my/sub/page/', 'facet', 'facet1');
		$this->assertEquals('http://site.com/my/sub/page/', $url);
	}

	public function testTypes()
	{
		register_post_type('post', array(
			'label' => 'Posts'
		));
		register_post_type('cpt', array(
			'label' => 'Cpts'
		));

		update_option('fields', array('post_type'));
		
		global $wp_query;

		$wp_query->facets = array(
			'post_type' => array(
				'post' => 3,
				'cpt' => 4
			)
		);

		$this->assertEquals(array(
			'post_type' => array(
				'available' => array(
					'post' => array(
						'count' => 3,
						'name' => 'Posts',
						'slug' => 'post',
						'font' => 21.0
					),
					'cpt' => array(
						'count'	=> 4,
						'name' => 'Cpts',
						'slug' => 'cpt',
						'font' => 24.0
					)
				),
				'selected' => array(),
				'total' => 7,
				'max' => 4,
				'min' => 3
			)
		), Faceting::all());
	}

	public function testAllTax()
	{
		register_taxonomy('tag', 'post');

		wp_insert_term('Tag 1', 'tag', array( 'slug' => 'tag1' ));

		wp_insert_term('Tag 2', 'tag', array( 'slug' => 'tag2' ));

		update_option('taxonomies', array('tag' => 1));
		update_option('fields', array());
		
		global $wp_query;

		$wp_query->facets = array(
			'tag' => array(
				'tag1' => 3,
				'tag2' => 4
			)
		);

		$this->assertEquals(array(
			'tag' => array(
				'available' => array(
					'tag1' => array(
						'count' => 3,
						'name' => 'Tag 1',
						'slug' => 'tag1',
						'font' => 21.0
					),
					'tag2' => array(
						'count'	=> 4,
						'name' => 'Tag 2',
						'slug' => 'tag2',
						'font' => 24.0
					)
				),
				'selected' => array(),
				'total' => 7,
				'max' => 4,
				'min' => 3
			)
		), Faceting::all());
	}

	public function testAllTaxSelected()
	{
		register_taxonomy('tag', 'post');

		wp_insert_term('Tag 1', 'tag', array( 'slug' => 'tag1' ));

		wp_insert_term('Tag 2', 'tag', array( 'slug' => 'tag2' ));

		update_option('taxonomies', array('tag' => 1));
		update_option('fields', array());

		global $wp_query;

		$wp_query->facets = array(
			'tag' => array(
				'tag1' => 3,
				'tag2' => 4
			)
		);

		$_GET = array(
			'es' => array(
				'tag' => array(
					'and' => array('tag1')
				)
			)
		);

		$this->assertEquals(array(
			'tag' => array(
				'available' => array(
					'tag2' => array(
						'count'	=> 4,
						'name' => 'Tag 2',
						'slug' => 'tag2',
						'font' => 24.0
					)
				),
				'selected' => array(
					'tag1' => array(
						'name' => 'Tag 1',
						'slug' => 'tag1'
					)
				),
				'total' => 4,
				'max' => 4,
				'min' => 4
			)
		), Faceting::all());
	}

	public function testAllNumeric()
	{
		update_option('fields', array('field1' => 1));
		update_option('numeric', array('field1' => 1));
		update_option('field1_range', '-10,10-20,20-');

		global $wp_query;

		$wp_query->facets = array(
			'field1' => array(
				'-10' => 0,
				'10-20' => 3,
				'20-' => 7
			)
		);

		$this->assertEquals(array(
			'field1' => array(
				'available' => array(
					'10-20' => array(
						'count'	=> 3,
						'slug' => '10-20',
						'font' => 18.0,
						'to' => '20',
						'from' => '10'
					),
					'20-' => array(
						'count'	=> 7,
						'slug' => '20-',
						'font' => 24.0,
						'to' => '',
						'from' => '20'
					)
				),
				'selected' => array(),
				'total' => 10,
				'max' => 7,
				'min' => 3
			)
		), Faceting::all());
	}

	public function testAllNumericSelected()
	{
		update_option('fields', array('field1' => 1));
		update_option('numeric', array('field1' => 1));
		update_option('field1_range', '-10,10-20,20-');

		global $wp_query;

		$wp_query->facets = array(
			'field1' => array(
				'-10' => 0,
				'10-20' => 3,
				'20-' => 7
			)
		);

		$_GET = array(
			'es' => array(
				'field1' => array(
					'and' => array('10-20')
				)
			)
		);

		$this->assertEquals(array(
			'field1' => array(
				'available' => array(
					'20-' => array(
						'count'	=> 7,
						'slug' => '20-',
						'font' => 24.0,
						'to' => '',
						'from' => '20'
					)
				),
				'selected' => array(
					'10-20' => array(
						'slug' => '10-20',
						'to' => '20',
						'from' => '10',
						'count' => 3
					)
				),
				'total' => 7,
				'min' => 7,
				'max' => 7
			)
		), Faceting::all());
	}
}
?>