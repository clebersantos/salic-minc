-- =========================================================================================
-- Autor: Rômulo Menhô Barbosa
-- Data de Criação: 19/04/2016
-- Descrição: Painel do Coordenador de Vinculada com projetos devolvidos pelo Minc
--            para nova análise validação.
-- =========================================================================================
-- Data de Alteração: 05/05/2016
-- Motivos : Retirar as restrições abaixo:
--           t.DtDistribuicao IS NULL, t.DtDevolucao, t.idAgenteParecerista IS NULL
-- =========================================================================================
IF OBJECT_ID ('vwPainelCoordenadorVinculadasReanalisar', 'V') IS NOT NULL
DROP VIEW vwPainelCoordenadorVinculadasReanalisar ;
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE VIEW dbo.vwPainelCoordenadorVinculadasReanalisar
AS
SELECT p.IdPRONAC, (p.AnoProjeto + p.Sequencial) AS NrProjeto, p.NomeProjeto,t.idProduto,r.Descricao AS Produto, 
       b.Area AS idArea, a.Descricao AS Area,b.Segmento as idSegmento,c.Descricao AS Segmento,      
       t.idDistribuirParecer, t.idOrgao,t.idAgenteParecerista,d.Descricao as Parecerista,
	   t.DtEnvio AS DtEnvioMincVinculada,DATEDIFF(DAY,t.DtEnvio,GETDATE()) AS qtDiasDistribuir,
	   e.dsJustificativa as JustComponente,e.dsJustificativaAnalise as JustDevolucaoPedido,t.Observacao as JustSecretaria,
	   (Select SUM(x.Ocorrencia*x.Quantidade*x.ValorUnitario) FROM SAC.dbo.tbPlanilhaProposta x WHERE p.idProjeto = x.idProjeto and x.FonteRecurso = 109 and x.idProduto = t.idProduto) AS Valor,
	   t.stPrincipal,t.FecharAnalise 
FROM tbDistribuirParecer                        AS t
INNER JOIN Projetos                             AS p ON t.idPRONAC  = p.IdPRONAC
INNER JOIN PlanoDistribuicaoProduto             AS b ON p.idProjeto = b.idProjeto and t.idProduto = b.idProduto
INNER JOIN Produto                              AS r ON b.idProduto = r.Codigo
INNER JOIN Area                                 AS a ON b.Area      = a.Codigo 
INNER JOIN Segmento                             AS c ON b.Segmento  = c.Codigo 
LEFT JOIN Agentes.dbo.Nomes                    AS d ON t.idAgenteParecerista = d.idAgente
LEFT JOIN BDCORPORATIVO.scSAC.tbRetirarDePauta AS e ON t.idPRONAC  = e.idPronac AND MotivoRetirada = 1 AND tpAcao = 3 AND stAtivo = 0
WHERE t.stEstado = 0 
      AND t.FecharAnalise= 2
      AND t.tipoanalise IN (3, 1) 
	  AND p.Situacao IN ('B11') 
GO 

GRANT  SELECT ON dbo.vwPainelCoordenadorVinculadasReanalisar  TO usuarios_internet
GO
