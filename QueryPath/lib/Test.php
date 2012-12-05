<?php
namespace QueryPath;

class Test extends \View{
    function init(){
        parent::init();

        $qp_loader = $this->add('QueryPath/Loader');

        // Begin with an HTML stub document (XHTML, actually), and navigate to the title.
        $html = $qp_loader->qp(\QueryPath::HTML_STUB, 'title')
            // Add some text to the title
            ->text('Example of QueryPath.')
            // Now look for the <body> element
            ->find(':root body')
            // Inside the body, add a title and paragraph.
            ->append('<h1>This is a test page</h1><p>Test text</p>')
            // Now we select the paragraph we just created inside the body
            ->children('p')
            // Add a 'class="some-class"' attribute to the paragraph
            ->attr('class', 'some-class')
            // And add a style attribute, too, setting the background color.
            ->css('background-color', '#eee')
            // Now go back to the paragraph again
            ->parent()
            // Before the paragraph and the title, add an empty table.
            ->prepend('<table id="my-table"></table>')
            // Now let's go to the table...
            ->find('#my-table')
            // Add a couple of empty rows
            ->append('<tr></tr><tr></tr>')
            // select the rows (both at once)
            ->children()
            // Add a CSS class to both rows
            ->addClass('table-row')
            // Now just get the first row (at position 0)
            ->eq(0)
            // Add a table header in the first row
            ->append('<th>This is the header</th>')
            // Now go to the next row
            ->next()
            // Add some data to this row
            ->append('<td>This is the data</td>')
            // Write it all out as HTML
            //->writeHTML();
            // Return HTML
            ->find(':root')->html();

        $this->add('Frame')->set($html);
    }
}
