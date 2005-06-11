LATEX    = latex </dev/null >/dev/null
DVIPS    = dvips </dev/null &>/dev/null
PDFLATEX = pdflatex </dev/null >/dev/null
THUMBPDF = thumbpdf </dev/null >/dev/null
RM       = rm -f
CP       = cp -f
MV       = mv -f
GZIP     = gzip --best

TEX  = rapport.tex

DVI  := $(TEX:.tex=.dvi)
PS   := $(TEX:.tex=.ps)
PSGZ := $(TEX:.tex=.ps.gz)
PDF  := $(TEX:.tex=.pdf)
TMP  := $(TEX:.tex=.aux) $(TEX:.tex=.log) $(TEX:.tex=.out) $(TEX:.tex=.toc) $(TEX:.tex=.tpt)

.SUFFIXES:
.SUFFIXES: .tex .dvi .ps .ps.gz .pdf
.PHONY:    default all dvi ps psgz pdf clean cleantmp

.tex.dvi:
	$(LATEX) $<

.dvi.ps:
	$(DVIPS) $< -o $@

.ps.ps.gz:
	$(CP) $< $<.save
	$(RM) $@
	$(GZIP) $<
	$(MV) $<.save $<

.tex.pdf:
	$(PDFLATEX) $<
	-$(THUMBPDF) $@
	$(PDFLATEX) $<

#default: all
default: pdf cleantmp

all:  dvi ps psgz pdf
dvi:  $(DVI)
ps:   $(PS)
psgz: $(PSGZ)
pdf:  $(PDF)

clean:
	$(RM) $(DVI) $(PS) $(PSGZ) $(PDF) $(TMP)

cleantmp:
	$(RM) $(TMP)
