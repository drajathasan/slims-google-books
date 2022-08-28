<?php
/**
 * @Created by          : Drajat Hasan
 * @Date                : 2022-08-28 16:57:27
 * @File name           : index.php
 */

defined('INDEX_AUTH') OR die('Direct access not allowed!');

use SLiMS\Integration\GoogleBooks;

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
// set dependency
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require __DIR__ . '/vendor/autoload.php';


// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

function httpQuery($query = [])
{
    return http_build_query(array_unique(array_merge($_GET, $query)));
}

function isbn($industryIdentifiers)
{
    if (isset($industryIdentifiers[1])) return $industryIdentifiers[1]['identifier'];
    if (isset($industryIdentifiers[0])) return $industryIdentifiers[0]['identifier'];
}

/* Action Area */

/* End Action Area */
if (!isset($_GET['result'])) {
?>
<div class="menuBox">
    <div class="menuBoxInner memberIcon">
        <div class="per_title">
            <h2>Pencarian/Pengambilan Bibliografi via Google Books</h2>
        </div>
        <div class="sub_section">
            <form name="search" id="search" target="booksIframe" action="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery() ?>" loadcontainer="searchResult" method="get" class="form-inline"><?php echo __('Search'); ?>
                <input type="hidden" name="mod" value="<?= str_replace(['\'', '"'], '', strip_tags($_GET['mod'])) ?>"/>
                <input type="hidden" name="id" value="<?= str_replace(['\'', '"'], '', strip_tags($_GET['id'])) ?>"/>
                <input type="hidden" name="result" value="ok"/>
                <input type="text" name="keywords" id="keywords" class="form-control col-md-3" />
                <select name="index" class="form-control ">
                    <option value=""><?php echo __('All fields'); ?></option>
                    <option value="isbn:"><?php echo __('ISBN/ISSN'); ?></option>
                    <option value="title:"><?php echo __('Title/Series Title'); ?></option>
                    <option value="author:"><?php echo __('Authors'); ?></option>
                </select>
                <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
            </form>
        </div>
        <div class="infoBox">
        * Pastikan Anda memiliki koneksi internet.
        </div>
    </div>
</div>
<iframe name="booksIframe" src="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery(['result' => 'yes']) ?>" class="w-100 h-auto" style="height: 100vh !important"></iframe>
<?php
} else {
    if (isset($_GET['keywords']))
    {
        // Start cache data
        $_SESSION['googleBooks'] = [];
        
        // Took from marcsru.php
        ob_start();
        $table = new simbio_table();
        $table->table_attr = 'align="center" class="s-table table" cellpadding="5" cellspacing="0"';
        echo  '<div class="p-3">
                <input value="'.__('Check All').'" class="check-all button btn btn-default" type="button"> 
                <input value="'.__('Uncheck All').'" class="uncheck-all button btn btn-default" type="button">
                <input type="submit" name="saveZ" class="s-btn btn btn-success save" value="' . __('Save Marc Records to Database') . '" /></div>';

        // table header
        $table->setHeader(array(__('Select'),__('Title'),__('ISBN/ISSN'),__('E-Book Preview')));
        $table->table_header_attr = 'class="dataListHeader alterCell font-weight-bold"';
        $table->setCellAttr(0, 0, '');

        // Table content
        $search = GoogleBooks::search(trim($_GET['index'] . $_GET['keywords']))->get();
        
        if ($search->count() > 0)
        {
            foreach ($search as $index => $books) 
            {
                $_SESSION['googleBooks'][$books['id']] = $books;

                // Extract all data in volumne info
                extract($books['volumeInfo']);

                // set book cover based on result
                $title_content = '<div class="media">
                            <img class="mr-3 rounded" src="'.$imageLinks['thumbnail'].'" alt="cover image" style="height:70px;">
                            <div class="media-body">
                            <div class="title">'.stripslashes($title).'</div><div class="authors">'.implode(', ', $authors??[]).'</div>
                            </div>
                        </div>';
            
                // Ebook preview
                $ebookLink = '<a href="' . $books['accessInfo']['webReaderLink'] . '" class="notAJAX openNewTab">Lihat</a>';

                $table->appendTableRow(array('',$title_content, isbn($industryIdentifiers??[]), $ebookLink));
                // set cell attribute
                $row_class = ($index%2 == 0)?'alterCell':'alterCell2';
                $table->setCellAttr($index, 0, 'class="'.$row_class.'" valign="top" style="width: 5px;"');
                $table->setCellAttr($index, 1, 'class="'.$row_class.'" valign="top" style="width: auto;"');
                $table->setCellAttr($index, 2, 'class="'.$row_class.'" valign="top" style="width: auto;"');
                $table->setCellAttr($index, 2, 'class="'.$row_class.'" valign="top" style="width: auto;"');
            }
        }
        // end table content

        echo $table->printTable();  
        $content = ob_get_clean();
        $js = <<<HTML
        <script>
            $(document).ready(function(){
                $('.openNewTab').click(function(e){
                    e.preventDefault();
                    parent.window.open($(this).attr('href'), '_blank');
                });
            });
        </script>
        HTML;
        include SB . 'admin/admin_template/notemplate_page_tpl.php';
        exit;
    }
}