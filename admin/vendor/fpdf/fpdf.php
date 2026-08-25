<?php
/*******************************************************************************
* FPDF                                                                         *
*                                                                              *
* Version: 1.86                                                                *
* Date:    2023-06-25                                                          *
* Author:  Olivier PLATHEY                                                     *
*******************************************************************************/

define('FPDF_VERSION', '1.86');

#[\AllowDynamicProperties]
class FPDF
{
    protected $page;               // current page number
    protected $n;                  // current object number
    protected $offsets;            // array of object offsets
    protected $buffer;             // buffer holding in-memory PDF
    protected $pages;              // array containing pages
    protected $state;              // current document state
    protected $compress;           // compression flag
    protected $k;                  // scale factor (number of points in user unit)
    protected $DefOrientation;     // default orientation
    protected $CurOrientation;     // current orientation
    protected $StdPageSizes;       // standard page sizes
    protected $DefPageSize;        // default page size
    protected $CurPageSize;        // current page size
    protected $CurRotation;        // current page rotation
    protected $PageInfo;           // page info array
    protected $wPt, $hPt;          // dimensions of page in points
    protected $w, $h;              // dimensions of page in user unit
    protected $lMargin;            // left margin
    protected $tMargin;            // top margin
    protected $rMargin;            // right margin
    protected $bMargin;            // page break margin
    protected $cMargin;            // cell margin
    protected $x, $y;              // current position in user unit
    protected $lasth;              // height of last printed cell
    protected $LineWidth;          // line width in user unit
    protected $fontpath;           // path containing fonts
    protected $CoreFonts;          // array of core font names
    protected $fonts;              // array of used fonts
    protected $FontFiles;          // array of font files
    protected $encodings;          // array of encodings
    protected $cmaps;              // array of ToUnicode CMaps
    protected $FontFamily;         // current font family
    protected $FontStyle;          // current font style
    protected $underline;          // underlining flag
    protected $CurrentFont;        // current font info
    protected $FontSizePt;         // current font size in points
    protected $FontSize;           // current font size in user unit
    protected $DrawColor;          // commands for drawing color
    protected $FillColor;          // commands for filling color
    protected $TextColor;          // commands for text color
    protected $ColorFlag;          // indicates whether fill and text colors are different
    protected $WithAlpha;          // indicates whether alpha channel is used
    protected $ws;                 // word spacing
    protected $images;             // array of used images
    protected $PageLinks;          // array of links in pages
    protected $links;              // array of internal links
    protected $AutoPageBreak;      // automatic page breaking
    protected $PageBreakTrigger;   // threshold for automatic page break
    protected $InHeader;           // flag set when processing header
    protected $InFooter;           // flag set when processing footer
    protected $AliasNbPages;       // alias for total number of pages
    protected $ZoomMode;           // zoom display mode
    protected $LayoutMode;         // layout display mode
    protected $metadata;           // document properties
    protected $PDFVersion;         // PDF version number

    function __construct($orientation='P', $unit='mm', $size='A4')
    {
        // Some checks
        $this->_dochecks();
        // Initialization of properties
        $this->state = 0;
        $this->page = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->pages = array();
        $this->PageInfo = array();
        $this->fonts = array();
        $this->FontFiles = array();
        $this->encodings = array();
        $this->cmaps = array();
        $this->images = array();
        $this->links = array();
        $this->InHeader = false;
        $this->InFooter = false;
        $this->AliasNbPages = '';
        $this->DefOrientation = $orientation;
        $this->CurOrientation = $orientation;
        $this->CurRotation = 0;
        $this->FontFamily = '';
        $this->FontStyle = '';
        $this->FontSizePt = 12;
        $this->underline = false;
        $this->DrawColor = '0 G';
        $this->FillColor = '0 g';
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->WithAlpha = false;
        $this->ws = 0;

        // Font path
        if(defined('FPDF_FONTPATH'))
            $this->fontpath = FPDF_FONTPATH;
        else
            $this->fontpath = __DIR__ . '/font/';

        // Core fonts
        $this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');

        // Scale factor
        if($unit=='pt')
            $this->k = 1;
        elseif($unit=='mm')
            $this->k = 72/25.4;
        elseif($unit=='cm')
            $this->k = 72/2.54;
        elseif($unit=='in')
            $this->k = 72;
        else
            $this->Error('Incorrect unit: '.$unit);

        // Page sizes
        $this->StdPageSizes = array('a3'=>array(841.89, 1190.55), 'a4'=>array(595.28, 841.89), 'a5'=>array(420.94, 595.28),
            'letter'=>array(612, 792), 'legal'=>array(612, 1008));
        $size = $this->_getpagesize($size);
        $this->DefPageSize = $size;
        $this->CurPageSize = $size;

        // Page orientation
        $orientation = strtolower($orientation);
        if($orientation=='p' || $orientation=='portrait') {
            $this->DefOrientation = 'P';
            $this->w = $size[0];
            $this->h = $size[1];
        } elseif($orientation=='l' || $orientation=='landscape') {
            $this->DefOrientation = 'L';
            $this->w = $size[1];
            $this->h = $size[0];
        } else {
            $this->Error('Incorrect orientation: '.$orientation);
        }

        $this->wPt = $this->w*$this->k;
        $this->hPt = $this->h*$this->k;

        // Page margins (1 cm)
        $margin = 28.35/$this->k;
        $this->SetMargins($margin, $margin);

        // Interior cell margin (1 mm)
        $this->cMargin = $margin/10;

        // Line width (0.2 mm)
        $this->LineWidth = .567/$this->k;

        // Automatic page break
        $this->SetAutoPageBreak(true, 2*$margin);

        // Default display mode
        $this->SetDisplayMode('default');

        // Enable compression
        $this->SetCompression(true);

        // Set default PDF version number
        $this->PDFVersion = '1.3';
    }

    function SetMargins($left, $top, $right=null)
    {
        $this->lMargin = $left;
        $this->tMargin = $top;
        if($right===null)
            $right = $left;
        $this->rMargin = $right;
    }

    function SetLeftMargin($margin)
    {
        $this->lMargin = $margin;
        if($this->page>0 && $this->x<$margin)
            $this->x = $margin;
    }

    function SetTopMargin($margin)
    {
        $this->tMargin = $margin;
    }

    function SetRightMargin($margin)
    {
        $this->rMargin = $margin;
    }

    function SetAutoPageBreak($auto, $margin=0)
    {
        $this->AutoPageBreak = $auto;
        $this->bMargin = $margin;
        $this->PageBreakTrigger = $this->h - $margin;
    }

    function SetDisplayMode($zoom, $layout='default')
    {
        if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
            $this->ZoomMode = $zoom;
        else
            $this->Error('Incorrect zoom display mode: '.$zoom);

        if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
            $this->LayoutMode = $layout;
        else
            $this->Error('Incorrect layout display mode: '.$layout);
    }

    function SetCompression($compress)
    {
        if(function_exists('gzcompress'))
            $this->compress = $compress;
        else
            $this->compress = false;
    }

    function SetTitle($title, $isUTF8=false)
    {
        $this->metadata['Title'] = $isUTF8 ? $title : utf8_encode($title);
    }

    function SetAuthor($author, $isUTF8=false)
    {
        $this->metadata['Author'] = $isUTF8 ? $author : utf8_encode($author);
    }

    function SetSubject($subject, $isUTF8=false)
    {
        $this->metadata['Subject'] = $isUTF8 ? $subject : utf8_encode($subject);
    }

    function SetKeywords($keywords, $isUTF8=false)
    {
        $this->metadata['Keywords'] = $isUTF8 ? $keywords : utf8_encode($keywords);
    }

    function SetCreator($creator, $isUTF8=false)
    {
        $this->metadata['Creator'] = $isUTF8 ? $creator : utf8_encode($creator);
    }

    function AliasNbPages($alias='{nb}')
    {
        $this->AliasNbPages = $alias;
    }

    function Error($msg)
    {
        throw new Exception('FPDF error: '.$msg);
    }

    function Open()
    {
        $this->state = 1;
    }

    function Close()
    {
        if($this->state==3)
            return;
        if($this->page==0)
            $this->AddPage();
        // Page footer
        $this->InFooter = true;
        $this->Footer();
        $this->InFooter = false;
        // Close page
        $this->_endpage();
        // Close document
        $this->_enddoc();
    }

    function AddPage($orientation='', $size='', $rotation=0)
    {
        if($this->state==0)
            $this->Open();

        $family = $this->FontFamily;
        $style = $this->FontStyle.($this->underline ? 'U' : '');
        $fontsize = $this->FontSizePt;
        $lw = $this->LineWidth;
        $dc = $this->DrawColor;
        $fc = $this->FillColor;
        $tc = $this->TextColor;
        $cf = $this->ColorFlag;

        if($this->page>0) {
            // Page footer
            $this->InFooter = true;
            $this->Footer();
            $this->InFooter = false;
            // Close page
            $this->_endpage();
        }

        // Start new page
        $this->_beginpage($orientation, $size, $rotation);

        // Set line cap style to square
        $this->_out('2 J');

        // Set line width
        $this->LineWidth = $lw;
        $this->_out(sprintf('%.2F w', $lw*$this->k));

        // Set font
        if($family)
            $this->SetFont($family, $style, $fontsize);

        // Set colors
        $this->DrawColor = $dc;
        if($dc!='0 G')
            $this->_out($dc);
        $this->FillColor = $fc;
        if($fc!='0 g')
            $this->_out($fc);
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;

        // Page header
        $this->InHeader = true;
        $this->Header();
        $this->InHeader = false;

        // Restore line width
        if($this->LineWidth!=$lw) {
            $this->LineWidth = $lw;
            $this->_out(sprintf('%.2F w', $lw*$this->k));
        }

        // Restore font
        if($family)
            $this->SetFont($family, $style, $fontsize);

        // Restore colors
        if($this->DrawColor!=$dc) {
            $this->DrawColor = $dc;
            $this->_out($dc);
        }
        if($this->FillColor!=$fc) {
            $this->FillColor = $fc;
            $this->_out($fc);
        }
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;
    }

    function Header()
    {
        // To be implemented in your own inherited class
    }

    function Footer()
    {
        // To be implemented in your own inherited class
    }

    function PageNo()
    {
        return $this->page;
    }

    function SetDrawColor($r, $g=null, $b=null)
    {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->DrawColor = sprintf('%.3F G', $r/255);
        else
            $this->DrawColor = sprintf('%.3F %.3F %.3F RG', $r/255, $g/255, $b/255);
        if($this->page>0)
            $this->_out($this->DrawColor);
    }

    function SetFillColor($r, $g=null, $b=null)
    {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->FillColor = sprintf('%.3F g', $r/255);
        else
            $this->FillColor = sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255);
        $this->ColorFlag = ($this->FillColor!=$this->TextColor);
        if($this->page>0)
            $this->_out($this->FillColor);
    }

    function SetTextColor($r, $g=null, $b=null)
    {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->TextColor = sprintf('%.3F g', $r/255);
        else
            $this->TextColor = sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255);
        $this->ColorFlag = ($this->FillColor!=$this->TextColor);
    }

    function GetStringWidth($s)
    {
        $s = (string)$s;
        $cw = &$this->CurrentFont['cw'];
        $w = 0;
        $l = strlen($s);
        for($i=0;$i<$l;$i++)
            $w += $cw[$s[$i]];
        return $w*$this->FontSize/1000;
    }

    function SetLineWidth($width)
    {
        $this->LineWidth = $width;
        if($this->page>0)
            $this->_out(sprintf('%.2F w', $width*$this->k));
    }

    function Line($x1, $y1, $x2, $y2)
    {
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F l S', $x1*$this->k, ($this->h-$y1)*$this->k, $x2*$this->k, ($this->h-$y2)*$this->k));
    }

    function Rect($x, $y, $w, $h, $style='')
    {
        if($style=='F')
            $op = 'f';
        elseif($style=='FD' || $style=='DF')
            $op = 'B';
        else
            $op = 'S';
        $this->_out(sprintf('%.2F %.2F %.2F %.2F re %s', $x*$this->k, ($this->h-$y)*$this->k, $w*$this->k, -$h*$this->k, $op));
    }

    function AddFont($family, $style='', $file='')
    {
        $family = strtolower($family);
        if($file=='')
            $file = str_replace(' ', '', $family).strtolower($style).'.php';
        $style = strtoupper($style);
        if($style=='IB')
            $style = 'BI';
        $fontkey = $family.$style;
        if(isset($this->fonts[$fontkey]))
            return;
        $info = $this->_loadfont($file);
        $info['i'] = count($this->fonts)+1;
        if(!empty($info['file'])) {
            if($info['type']=='TrueType')
                $this->FontFiles[$info['file']] = array('length1'=>$info['originalsize']);
            else
                $this->FontFiles[$info['file']] = array('length1'=>$info['size1'], 'length2'=>$info['size2']);
        }
        $this->fonts[$fontkey] = $info;
    }

    function SetFont($family, $style='', $size=0)
    {
        if($family=='')
            $family = $this->FontFamily;
        else
            $family = strtolower($family);
        $style = strtoupper($style);
        if(strpos($style, 'U')!==false) {
            $this->underline = true;
            $style = str_replace('U', '', $style);
        } else
            $this->underline = false;
        if($style=='IB')
            $style = 'BI';
        if($size==0)
            $size = $this->FontSizePt;

        if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size)
            return;

        $fontkey = $family.$style;
        if(!isset($this->fonts[$fontkey])) {
            if(in_array($family, $this->CoreFonts)) {
                if($family=='symbol' || $family=='zapfdingbats')
                    $style = '';
                $fontkey = $family.$style;
                if(!isset($this->fonts[$fontkey]))
                    $this->_loadcorefont($fontkey);
            } else
                $this->Error('Undefined font: '.$family.' '.$style);
        }

        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size/$this->k;
        $this->CurrentFont = &$this->fonts[$fontkey];
        if($this->page>0)
            $this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
    }

    function SetFontSize($size)
    {
        if($this->FontSizePt==$size)
            return;
        $this->FontSizePt = $size;
        $this->FontSize = $size/$this->k;
        if($this->page>0 && isset($this->CurrentFont['i']))
            $this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
    }

    function AddLink()
    {
        $n = count($this->links)+1;
        $this->links[$n] = array(0, 0);
        return $n;
    }

    function SetLink($link, $y=0, $page=-1)
    {
        if($y==-1)
            $y = $this->y;
        if($page==-1)
            $page = $this->page;
        $this->links[$link] = array($page, $y);
    }

    function Link($x, $y, $w, $h, $link)
    {
        $this->PageLinks[$this->page][] = array($x*$this->k, $this->hPt-$y*$this->k, $w*$this->k, $h*$this->k, $link);
    }

    function Text($x, $y, $txt)
    {
        if(!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $txt = (string)$txt;
        $s = sprintf('BT %.2F %.2F Td (%s) Tj ET', $x*$this->k, ($this->h-$y)*$this->k, $this->_escape($txt));
        if($this->underline && $txt!='')
            $s .= ' '.$this->_dounderline($x, $y, $txt);
        if($this->ColorFlag)
            $s = 'q '.$this->TextColor.' '.$s.' Q';
        $this->_out($s);
    }

    function AcceptPageBreak()
    {
        return $this->AutoPageBreak;
    }

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        if(!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $txt = (string)$txt;
        $k = $this->k;
        if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak()) {
            $x = $this->x;
            $ws = $this->ws;
            if($ws>0) {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation, $this->CurPageSize, $this->CurRotation);
            $this->x = $x;
            if($ws>0) {
                $this->ws = $ws;
                $this->_out(sprintf('%.3F Tw', $ws*$k));
            }
        }
        if($w==0)
            $w = $this->w - $this->rMargin - $this->x;
        $s = '';
        if($fill || $border==1) {
            if($fill)
                $op = ($border==1) ? 'B' : 'f';
            else
                $op = 'S';
            $s = sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k, $op);
        }
        if(is_string($border)) {
            $x = $this->x;
            $y = $this->y;
            if(strpos($border, 'L')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$k, ($this->h-$y)*$k, $x*$k, ($this->h-($y+$h))*$k);
            if(strpos($border, 'T')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-$y)*$k);
            if(strpos($border, 'R')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', ($x+$w)*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
            if(strpos($border, 'B')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$k, ($this->h-($y+$h))*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
        }
        if($txt!=='') {
            if(!empty($align)) {
                if($align=='R')
                    $dx = $w - $this->cMargin - $this->GetStringWidth($txt);
                elseif($align=='C')
                    $dx = ($w - $this->GetStringWidth($txt))/2;
                else
                    $dx = $this->cMargin;
            } else
                $dx = $this->cMargin;
            if($this->ColorFlag)
                $s .= 'q '.$this->TextColor.' ';
            $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET', ($this->x+$dx)*$k, ($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k, $this->_escape($txt));
            if($this->underline)
                $s .= ' '.$this->_dounderline($this->x+$dx, $this->y+.5*$h+.3*$this->FontSize, $txt);
            if($this->ColorFlag)
                $s .= ' Q';
            if($link)
                $this->Link($this->x+$dx, $this->y+.5*$h-.5*$this->FontSize, $this->GetStringWidth($txt), $this->FontSize, $link);
        }
        if($s)
            $this->_out($s);
        $this->lasth = $h;
        if($ln>0) {
            $this->y += $h;
            if($ln==1)
                $this->x = $this->lMargin;
        } else
            $this->x += $w;
    }

    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
    {
        if(!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $cw = &$this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $b = 0;
        if($border) {
            if($border==1) {
                $border = 'LRTB';
                $b = 'LRTB';
                $b2 = 'LR';
            } else {
                $b2 = '';
                if(strpos($border, 'L')!==false)
                    $b2 .= 'L';
                if(strpos($border, 'R')!==false)
                    $b2 .= 'R';
                $b = (strpos($border, 'T')!==false) ? $b2.'T' : $b2;
            }
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $ns = 0;
        $nl = 1;
        while($i<$nb) {
            $c = $s[$i];
            if($c=="\n") {
                if($this->ws>0) {
                    $this->ws = 0;
                    $this->_out('0 Tw');
                }
                $this->Cell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if($border && $nl==2)
                    $b = $b2;
                continue;
            }
            if($c==' ') {
                $sep = $i;
                $ls = $l;
                $ns++;
            }
            $l += $cw[$c];
            if($l>$wmax) {
                if($sep==-1) {
                    if($i==$j)
                        $i++;
                    if($this->ws>0) {
                        $this->ws = 0;
                        $this->_out('0 Tw');
                    }
                    $this->Cell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
                } else {
                    if($align=='J') {
                        $this->ws = ($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
                        $this->_out(sprintf('%.3F Tw', $this->ws*$this->k));
                    }
                    $this->Cell($w, $h, substr($s, $j, $sep-$j), $b, 2, $align, $fill);
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if($border && $nl==2)
                    $b = $b2;
            } else
                $i++;
        }
        if($this->ws>0) {
            $this->ws = 0;
            $this->_out('0 Tw');
        }
        if($border && strpos($border, 'B')!==false)
            $b .= 'B';
        $this->Cell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
        $this->x = $this->lMargin;
    }

    function Write($h, $txt, $link='')
    {
        if(!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $cw = &$this->CurrentFont['cw'];
        $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb) {
            $c = $s[$i];
            if($c=="\n") {
                $this->Cell($w, $h, substr($s, $j, $i-$j), 0, 2, '', false, $link);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                if($nl==1) {
                    $this->x = $this->lMargin;
                    $w = $this->w - $this->rMargin - $this->x;
                    $wmax = ($w - 2*$this->cMargin)*1000/$this->FontSize;
                }
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += $cw[$c];
            if($l>$wmax) {
                if($sep==-1) {
                    if($this->x>$this->lMargin) {
                        $this->x = $this->lMargin;
                        $this->y += $h;
                        $w = $this->w - $this->rMargin - $this->x;
                        $wmax = ($w - 2*$this->cMargin)*1000/$this->FontSize;
                        $i++;
                        $nl++;
                        continue;
                    }
                    if($i==$j)
                        $i++;
                    $this->Cell($w, $h, substr($s, $j, $i-$j), 0, 2, '', false, $link);
                } else {
                    $this->Cell($w, $h, substr($s, $j, $sep-$j), 0, 2, '', false, $link);
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                if($nl==1) {
                    $this->x = $this->lMargin;
                    $w = $this->w - $this->rMargin - $this->x;
                    $wmax = ($w - 2*$this->cMargin)*1000/$this->FontSize;
                }
                $nl++;
            } else
                $i++;
        }
        if($i!=$j)
            $this->Cell($this->GetStringWidth(substr($s, $j)), $h, substr($s, $j), 0, 0, '', false, $link);
    }

    function Ln($h=null)
    {
        $this->x = $this->lMargin;
        if($h===null)
            $this->y += $this->lasth;
        else
            $this->y += $h;
    }

    function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='')
    {
        if(!isset($this->images[$file])) {
            if($type=='') {
                $pos = strrpos($file, '.');
                if(!$pos)
                    $this->Error('Image file has no extension and no type was specified: '.$file);
                $type = substr($file, $pos+1);
            }
            $type = strtolower($type);
            if($type=='jpeg')
                $type = 'jpg';
            $mtd = '_parse'.$type;
            if(!method_exists($this, $mtd))
                $this->Error('Unsupported image type: '.$type);
            $info = $this->$mtd($file);
            $info['i'] = count($this->images)+1;
            $this->images[$file] = $info;
        } else
            $info = $this->images[$file];

        if($w==0 && $h==0) {
            $w = -96;
            $h = -96;
        }
        if($w<0)
            $w = -$info['w']*72/$w/$this->k;
        if($h<0)
            $h = -$info['h']*72/$h/$this->k;
        if($w==0)
            $w = $h*$info['w']/$info['h'];
        if($h==0)
            $h = $w*$info['h']/$info['w'];

        if($y===null) {
            if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak()) {
                $x2 = $this->x;
                $this->AddPage($this->CurOrientation, $this->CurPageSize, $this->CurRotation);
                $this->x = $x2;
            }
            $y = $this->y;
            $this->y += $h;
        }

        if($x===null)
            $x = $this->x;

        $this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q', $w*$this->k, $h*$this->k, $x*$this->k, ($this->h-($y+$h))*$this->k, $info['i']));
        if($link)
            $this->Link($x, $y, $w, $h, $link);
    }

    function GetPageWidth()
    {
        return $this->w;
    }

    function GetPageHeight()
    {
        return $this->h;
    }

    function GetX()
    {
        return $this->x;
    }

    function SetX($x)
    {
        if($x>=0)
            $this->x = $x;
        else
            $this->x = $this->w + $x;
    }

    function GetY()
    {
        return $this->y;
    }

    function SetY($y, $resetX=true)
    {
        if($y>=0)
            $this->y = $y;
        else
            $this->y = $this->h + $y;
        if($resetX)
            $this->x = $this->lMargin;
    }

    function SetXY($x, $y)
    {
        $this->SetX($x);
        $this->SetY($y, false);
    }

    function Output($dest='', $name='', $isUTF8=false)
    {
        $this->Close();
        if($dest=='') {
            if($name=='') {
                $name = 'doc.pdf';
                $dest = 'I';
            } else
                $dest = 'F';
        }

        switch(strtoupper($dest)) {
            case 'I':
                $this->_checkoutput();
                if(PHP_SAPI!='cli') {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; '.$this->_httpencode('filename', $name, $isUTF8));
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                }
                echo $this->buffer;
                break;
            case 'D':
                $this->_checkoutput();
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; '.$this->_httpencode('filename', $name, $isUTF8));
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                echo $this->buffer;
                break;
            case 'F':
                $f = fopen($name, 'wb');
                if(!$f)
                    $this->Error('Unable to create output file: '.$name);
                fwrite($f, $this->buffer, strlen($this->buffer));
                fclose($f);
                break;
            case 'S':
                return $this->buffer;
            default:
                $this->Error('Incorrect output destination: '.$dest);
        }
        return '';
    }

    protected function _dochecks()
    {
        if(sprintf('%.1F', 1.0)!='1.0')
            $this->Error('This version of PHP is not supported');
    }

    protected function _checkoutput()
    {
        if(PHP_SAPI!='cli') {
            if(headers_sent($file, $line))
                $this->Error("Some data has already been output, can't send PDF file (output started at $file:$line)");
        }
        if(ob_get_length()) {
            if(preg_match('/^(\xEF\xBB\xBF)?\s*$/', ob_get_contents())) {
                ob_clean();
            } else
                $this->Error("Some data has already been output, can't send PDF file");
        }
    }

    protected function _getpagesize($size)
    {
        if(is_string($size)) {
            $a = strtolower($size);
            if(!isset($this->StdPageSizes[$a]))
                $this->Error('Unknown page size: '.$size);
            $a = $this->StdPageSizes[$a];
            return array($a[0]/$this->k, $a[1]/$this->k);
        } else {
            if($size[0]>$size[1])
                return array($size[1], $size[0]);
            else
                return $size;
        }
    }

    protected function _beginpage($orientation, $size, $rotation)
    {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';

        if($orientation=='')
            $orientation = $this->DefOrientation;
        else
            $orientation = strtoupper($orientation[0]);
        if($size=='')
            $size = $this->DefPageSize;
        else
            $size = $this->_getpagesize($size);
        if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1]) {
            if($orientation=='P') {
                $this->w = $size[0];
                $this->h = $size[1];
            } else {
                $this->w = $size[1];
                $this->h = $size[0];
            }
            $this->wPt = $this->w*$this->k;
            $this->hPt = $this->h*$this->k;
            $this->PageBreakTrigger = $this->h - $this->bMargin;
            $this->CurOrientation = $orientation;
            $this->CurPageSize = $size;
        }
        if($orientation!=$this->DefOrientation || $size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1])
            $this->PageInfo[$this->page]['size'] = array($this->wPt, $this->hPt);

        if($rotation!=0) {
            if($rotation%90!=0)
                $this->Error('Incorrect rotation angle: '.$rotation);
            $this->CurRotation = $rotation;
            $this->PageInfo[$this->page]['rotation'] = $rotation;
        }
    }

    protected function _endpage()
    {
        $this->state = 1;
    }

    protected function _loadcorefont($fontkey)
    {
        if(strpos($fontkey, 'courier')===0) {
            $name = 'Courier';
            $cw = array(
                0=>600,1=>600,2=>600,3=>600,4=>600,5=>600,6=>600,7=>600,8=>600,9=>600,10=>600,11=>600,12=>600,13=>600,14=>600,15=>600,
                16=>600,17=>600,18=>600,19=>600,20=>600,21=>600,22=>600,23=>600,24=>600,25=>600,26=>600,27=>600,28=>600,29=>600,30=>600,31=>600,
                32=>600,33=>600,34=>600,35=>600,36=>600,37=>600,38=>600,39=>600,40=>600,41=>600,42=>600,43=>600,44=>600,45=>600,46=>600,47=>600,
                48=>600,49=>600,50=>600,51=>600,52=>600,53=>600,54=>600,55=>600,56=>600,57=>600,58=>600,59=>600,60=>600,61=>600,62=>600,63=>600,
                64=>600,65=>600,66=>600,67=>600,68=>600,69=>600,70=>600,71=>600,72=>600,73=>600,74=>600,75=>600,76=>600,77=>600,78=>600,79=>600,
                80=>600,81=>600,82=>600,83=>600,84=>600,85=>600,86=>600,87=>600,88=>600,89=>600,90=>600,91=>600,92=>600,93=>600,94=>600,95=>600,
                96=>600,97=>600,98=>600,99=>600,100=>600,101=>600,102=>600,103=>600,104=>600,105=>600,106=>600,107=>600,108=>600,109=>600,110=>600,111=>600,
                112=>600,113=>600,114=>600,115=>600,116=>600,117=>600,118=>600,119=>600,120=>600,121=>600,122=>600,123=>600,124=>600,125=>600,126=>600,127=>600
            );
            if(strpos($fontkey, 'B')!==false)
                $name .= '-Bold';
            if(strpos($fontkey, 'I')!==false)
                $name .= '-Oblique';
        } elseif(strpos($fontkey, 'helvetica')===0) {
            $name = 'Helvetica';
            $cw = array(
                0=>556,1=>556,2=>556,3=>556,4=>556,5=>556,6=>556,7=>556,8=>556,9=>556,10=>556,11=>556,12=>556,13=>556,14=>556,15=>556,
                16=>556,17=>556,18=>556,19=>556,20=>556,21=>556,22=>556,23=>556,24=>556,25=>556,26=>556,27=>556,28=>556,29=>556,30=>556,31=>556,
                32=>278,33=>278,34=>355,35=>556,36=>556,37=>889,38=>667,39=>191,40=>333,41=>333,42=>389,43=>584,44=>278,45=>333,46=>278,47=>278,
                48=>556,49=>556,50=>556,51=>556,52=>556,53=>556,54=>556,55=>556,56=>556,57=>556,58=>278,59=>278,60=>584,61=>584,62=>584,63=>556,
                64=>1015,65=>667,66=>667,67=>722,68=>722,69=>667,70=>611,71=>778,72=>722,73=>278,74=>500,75=>667,76=>556,77=>833,78=>722,79=>778,
                80=>667,81=>778,82=>722,83=>667,84=>611,85=>722,86=>667,87=>944,88=>667,89=>667,90=>611,91=>278,92=>278,93=>278,94=>469,95=>556,
                96=>333,97=>556,98=>556,99=>500,100=>556,101=>556,102=>278,103=>556,104=>556,105=>222,106=>222,107=>500,108=>222,109=>833,110=>556,111=>556,
                112=>556,113=>556,114=>333,115=>500,116=>278,117=>556,118=>500,119=>722,120=>500,121=>500,122=>500,123=>334,124=>260,125=>334,126=>584,127=>556
            );
            if(strpos($fontkey, 'B')!==false)
                $name .= '-Bold';
            if(strpos($fontkey, 'I')!==false)
                $name .= '-Oblique';
        } elseif(strpos($fontkey, 'times')===0) {
            $name = 'Times';
            $cw = array(
                0=>500,1=>500,2=>500,3=>500,4=>500,5=>500,6=>500,7=>500,8=>500,9=>500,10=>500,11=>500,12=>500,13=>500,14=>500,15=>500,
                16=>500,17=>500,18=>500,19=>500,20=>500,21=>500,22=>500,23=>500,24=>500,25=>500,26=>500,27=>500,28=>500,29=>500,30=>500,31=>500,
                32=>250,33=>333,34=>408,35=>500,36=>500,37=>833,38=>778,39=>180,40=>333,41=>333,42=>500,43=>564,44=>250,45=>333,46=>250,47=>278,
                48=>500,49=>500,50=>500,51=>500,52=>500,53=>500,54=>500,55=>500,56=>500,57=>500,58=>278,59=>278,60=>564,61=>564,62=>564,63=>444,
                64=>921,65=>722,66=>667,67=>667,68=>722,69=>611,70=>556,71=>722,72=>722,73=>333,74=>389,75=>722,76=>611,77=>889,78=>722,79=>722,
                80=>556,81=>722,82=>667,83=>556,84=>611,85=>722,86=>556,87=>833,88=>722,89=>722,90=>556,91=>333,92=>278,93=>333,94=>469,95=>500,
                96=>333,97=>444,98=>500,99=>444,100=>500,101=>444,102=>333,103=>500,104=>500,105=>278,106=>278,107=>500,108=>278,109=>778,110=>500,111=>500,
                112=>500,113=>500,114=>333,115=>389,116=>278,117=>500,118=>500,119=>722,120=>500,121=>500,122=>444,123=>480,124=>200,125=>480,126=>541,127=>500
            );
            if(strpos($fontkey, 'B')!==false)
                $name .= '-Bold';
            if(strpos($fontkey, 'I')!==false)
                $name .= '-Italic';
        } else {
            $name = $fontkey;
            $cw = array_fill(0, 256, 500);
        }
        
        $cw_chars = array();
        foreach($cw as $k => $v) {
            $cw_chars[chr($k)] = $v;
        }

        $this->fonts[$fontkey] = array(
            'i' => count($this->fonts)+1,
            'type' => 'core',
            'name' => $name,
            'up' => -100,
            'ut' => 50,
            'cw' => $cw_chars
        );
    }

    protected function _loadfont($font)
    {
        // Load a font definition file
        if(!file_exists($this->fontpath.$font))
            $this->Error('Font file not found: '.$font);
        include($this->fontpath.$font);
        if(!isset($name))
            $this->Error('Could not load font file');
        if(isset($enc))
            $enc = strtolower($enc);
        if(isset($type))
            $type = strtolower($type);
        return get_defined_vars();
    }

    protected function _escape($s)
    {
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace('(', '\\(', $s);
        $s = str_replace(')', '\\)', $s);
        $s = str_replace("\r", '\\r', $s);
        return $s;
    }

    protected function _dounderline($x, $y, $txt)
    {
        $up = $this->CurrentFont['up'];
        $ut = $this->CurrentFont['ut'];
        $w = $this->GetStringWidth($txt)+$this->ws*substr_count($txt, ' ');
        return sprintf('%.2F %.2F %.2F %.2F re f', $x*$this->k, ($this->h-($y-$up/1000*$this->FontSize))*$this->k, $w*$this->k, -$ut/1000*$this->FontSizePt);
    }

    protected function _out($s)
    {
        if($this->state==2)
            $this->pages[$this->page] .= $s."\n";
        elseif($this->state==1)
            $this->_put($s);
        elseif($this->state==0)
            $this->Error('No page has been added yet');
        elseif($this->state==3)
            $this->Error('The document is closed');
    }

    protected function _put($s)
    {
        $this->buffer .= $s."\n";
    }

    protected function _getoffset()
    {
        return strlen($this->buffer);
    }

    protected function _newobj($n=null)
    {
        if($n===null)
            $n = ++$this->n;
        $this->offsets[$n] = $this->_getoffset();
        $this->_put($n.' 0 obj');
    }

    protected function _putstream($data)
    {
        $this->_put('stream');
        $this->_put($data);
        $this->_put('endstream');
    }

    protected function _putpages()
    {
        $nb = $this->page;
        if(!empty($this->AliasNbPages)) {
            for($n=1;$n<=$nb;$n++)
                $this->pages[$n] = str_replace($this->AliasNbPages, $nb, $this->pages[$n]);
        }
        if($this->DefOrientation=='P') {
            $wPt = $this->DefPageSize[0]*$this->k;
            $hPt = $this->DefPageSize[1]*$this->k;
        } else {
            $wPt = $this->DefPageSize[1]*$this->k;
            $hPt = $this->DefPageSize[0]*$this->k;
        }
        for($n=1;$n<=$nb;$n++) {
            $this->_newobj();
            $this->_put('<</Type /Page');
            $this->_put('/Parent 1 0 R');
            if(isset($this->PageInfo[$n]['size']))
                $this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]', $this->PageInfo[$n]['size'][0], $this->PageInfo[$n]['size'][1]));
            if(isset($this->PageInfo[$n]['rotation']))
                $this->_put('/Rotate '.$this->PageInfo[$n]['rotation']);
            $this->_put('/Resources 2 0 R');
            $this->_put('/Contents '.($this->n+1).' 0 R>>');
            $this->_put('endobj');

            // Page content stream
            $p = $this->pages[$n];
            if($this->compress) {
                $p = gzcompress($p);
                $this->_newobj();
                $this->_put('<</Filter /FlateDecode /Length '.strlen($p).'>>');
                $this->_putstream($p);
                $this->_put('endobj');
            } else {
                $this->_newobj();
                $this->_put('<</Length '.strlen($p).'>>');
                $this->_putstream($p);
                $this->_put('endobj');
            }
        }

        // Pages root
        $this->offsets[1] = $this->_getoffset();
        $this->_put('1 0 R is catalog');
        $this->_put('1 0 obj');
        $this->_put('<</Type /Pages');
        $kids = '/Kids [';
        for($n=1;$n<=$nb;$n++)
            $kids .= (3+2*($n-1)).' 0 R ';
        $this->_put($kids.']');
        $this->_put('/Count '.$nb);
        $this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]', $wPt, $hPt));
        $this->_put('>>');
        $this->_put('endobj');
    }

    protected function _putfonts()
    {
        foreach($this->fonts as $k=>$font) {
            $this->_newobj();
            $this->fonts[$k]['n'] = $this->n;
            $name = $font['name'];
            $this->_put('<</Type /Font');
            $this->_put('/Subtype /Type1');
            $this->_put('/BaseFont /'.$name);
            $this->_put('/Encoding /WinAnsiEncoding');
            $this->_put('>>');
            $this->_put('endobj');
        }
    }

    protected function _putresources()
    {
        $this->_putfonts();
        // Resource dictionary
        $this->offsets[2] = $this->_getoffset();
        $this->_put('2 0 obj');
        $this->_put('<</ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
        $this->_put('/Font <<');
        foreach($this->fonts as $font)
            $this->_put('/F'.$font['i'].' '.$font['n'].' 0 R');
        $this->_put('>>');
        $this->_put('>>');
        $this->_put('endobj');
    }

    protected function _putinfo()
    {
        $this->_put('/Producer (FPDF '.FPDF_VERSION.')');
        if(!empty($this->metadata['Title']))
            $this->_put('/Title ('.$this->_escape($this->metadata['Title']).')');
        if(!empty($this->metadata['Subject']))
            $this->_put('/Subject ('.$this->_escape($this->metadata['Subject']).')');
        if(!empty($this->metadata['Author']))
            $this->_put('/Author ('.$this->_escape($this->metadata['Author']).')');
        if(!empty($this->metadata['Keywords']))
            $this->_put('/Keywords ('.$this->_escape($this->metadata['Keywords']).')');
        if(!empty($this->metadata['Creator']))
            $this->_put('/Creator ('.$this->_escape($this->metadata['Creator']).')');
        $this->_put('/CreationDate (D:'.date('YmdHis').')');
    }

    protected function _putcatalog()
    {
        $this->_newobj();
        $this->_put('<</Type /Catalog');
        $this->_put('/Pages 1 0 R');
        if($this->ZoomMode=='fullpage')
            $this->_put('/OpenAction [3 0 R /Fit]');
        elseif($this->ZoomMode=='fullwidth')
            $this->_put('/OpenAction [3 0 R /FitH null]');
        elseif($this->ZoomMode=='real')
            $this->_put('/OpenAction [3 0 R /XYZ null null 1]');
        elseif(!is_string($this->ZoomMode))
            $this->_put('/OpenAction [3 0 R /XYZ null null '.($this->ZoomMode/100).']');
        if($this->LayoutMode=='single')
            $this->_put('/PageLayout /SinglePage');
        elseif($this->LayoutMode=='continuous')
            $this->_put('/PageLayout /OneColumn');
        elseif($this->LayoutMode=='two')
            $this->_put('/PageLayout /TwoColumnLeft');
        $this->_put('>>');
        $this->_put('endobj');
    }

    protected function _enddoc()
    {
        $this->_putheader();
        $this->_putpages();
        $this->_putresources();
        // Info
        $this->_newobj();
        $this->_put('<<');
        $this->_putinfo();
        $this->_put('>>');
        $this->_put('endobj');
        // Catalog
        $this->_putcatalog();
        // Cross-ref
        $o = $this->_getoffset();
        $this->_put('xref');
        $this->_put('0 '.($this->n+1));
        $this->_put('0000000000 65535 f ');
        for($i=1;$i<=$this->n;$i++)
            $this->_put(sprintf('%010d 00000 n ', $this->offsets[$i]));
        // Trailer
        $this->_put('trailer');
        $this->_put('<</Size '.($this->n+1));
        $this->_put('/Root '.$this->n.' 0 R');
        $this->_put('/Info '.($this->n-1).' 0 R>>');
        $this->_put('startxref');
        $this->_put($o);
        $this->_put('%%EOF');
        $this->state = 3;
    }

    protected function _putheader()
    {
        $this->_put('%PDF-'.$this->PDFVersion);
    }

    protected function _httpencode($param, $value, $isUTF8)
    {
        if(!preg_match('/[\x80-\xFF]/', $value))
            return $param.'="'.addslashes($value).'"';
        if(!$isUTF8)
            $value = utf8_encode($value);
        return $param."*=UTF-8''".rawurlencode($value);
    }
}
?>
