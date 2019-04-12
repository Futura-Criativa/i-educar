<?php

require_once "include/clsBase.inc.php";
require_once "include/clsCadastro.inc.php";
require_once "include/clsBanco.inc.php";
require_once "include/pmieducar/geral.inc.php";
require_once 'lib/Portabilis/Date/Utils.php';
require_once 'lib/App/Model/Educacenso.php';

class clsIndexBase extends clsBase
{
    public function Formular()
    {
        $this->SetTitulo("{$this->_instituicao} i-Educar - Turno do aluno");
        $this->processoAp = "578";
        $this->addEstilo("localizacaoSistema");
    }
}

class indice extends clsCadastro
{
    public $cod_matricula;
    public $ref_cod_aluno;
    public $turno;

    public function Formular()
    {
        $this->nome_url_cancelar = "Voltar";
        $this->url_cancelar = "educar_matricula_det.php?cod_matricula={$this->cod_matricula}";

        $this->breadcrumb('Turno do aluno', [
            $_SERVER['SERVER_NAME'] . "/intranet" => "Início",
            "educar_index.php" => "Escola",
        ]);
    }

    public function Inicializar()
    {
        $this->cod_matricula = $_GET["ref_cod_matricula"];
        $this->ref_cod_aluno = $_GET["ref_cod_aluno"];

        $this->validaPermissao();
        $this->validaParametros();
        return 'Editar';
    }

    public function Gerar()
    {
        $this->campoOculto("cod_matricula", $this->cod_matricula);
        $this->campoOculto("ref_cod_aluno", $this->ref_cod_aluno);

        $obj_aluno = new clsPmieducarAluno();
        $lst_aluno = $obj_aluno->lista($this->ref_cod_aluno, null, null, null, null, null, null, null, null, null, 1);
        if (is_array($lst_aluno)) {
            $det_aluno = array_shift($lst_aluno);
            $this->nm_aluno = $det_aluno["nome_aluno"];
            $this->campoRotulo("nm_aluno", "Aluno", $this->nm_aluno);
        }
        $enturmacoes = new clsPmieducarMatriculaTurma();
        $enturmacoes = $enturmacoes->lista(
            $this->cod_matricula, null, null,
            null, null, null, null, null, 1, null, null, null,
            null, null, null, null, null, null, null, null, false,
            null, null, null, false, false, false, null, null,
            false, null, false, false, false
        );

        $turnos = [
            0 => 'Selecione',
            clsPmieducarTurma::TURNO_MATUTINO => 'Matutino',
            clsPmieducarTurma::TURNO_VESPERTINO => 'Vespertino'
        ];

        foreach ($enturmacoes as $enturmacao) {
            $turma         = new clsPmieducarTurma($enturmacao['ref_cod_turma']);
            $turma         = $turma->detalhe();
            if ($turma['turma_turno_id'] != clsPmieducarTurma::TURNO_INTEGRAL) {
                continue;
            }
            
            $this->campoLista("turno[{$enturmacao['ref_cod_turma']}-{$enturmacao['sequencial']}]", "Turno do aluno na turma: {$enturmacao['nm_turma']}", $turnos, $enturmacao['turno_id'], '', false, '', '', false, false);
        }
    }

    public function Editar()
    {
        $this->validaPermissao();
        $this->validaParametros();

        foreach ($this->turno as $codTurmaESequencial => $turno) {
            // Necessário pois chave é Turma + Matrícula + Sequencial
            $codTurmaESequencial = explode('-', $codTurmaESequencial);
            $codTurma = $codTurmaESequencial[0];
            $sequencial = $codTurmaESequencial[1];
            $obj = new clsPmieducarMatriculaTurma($this->cod_matricula, $codTurma, $this->pessoa_logada);
            $obj->sequencial = $sequencial;
            $obj->turno_id = $turno;
            $obj->edita();
        }

        $this->mensagem .= "Turno atualizado com sucesso.<br>";
        return true;
    }

    private function validaPermissao()
    {
        $obj_permissoes = new clsPermissoes();
        $obj_permissoes->permissao_cadastra(578, $this->pessoa_logada, 7, "educar_matricula_lst.php?ref_cod_aluno={$this->ref_cod_aluno}");
    }

    private function validaParametros()
    {
        $obj_matricula = new clsPmieducarMatricula($this->cod_matricula);
        $det_matricula = $obj_matricula->detalhe();

        if (!$det_matricula) {
            $this->simpleRedirect("educar_matricula_lst.php?ref_cod_aluno={$this->ref_cod_aluno}");
        }

    }
}

// cria uma extensao da classe base
$pagina = new clsIndexBase();
// cria o conteudo
$miolo = new indice();
// adiciona o conteudo na clsBase
$pagina->addForm($miolo);
// gera o html
$pagina->MakeAll();